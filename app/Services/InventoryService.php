<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Reserve stock for Work Order approved items
     */
    public function reserveStockForWorkOrder(WorkOrder $workOrder): void
    {
        DB::transaction(function () use ($workOrder) {
            foreach ($workOrder->partItems as $item) {
                if ($item->spare_part_id && in_array($item->fulfillment_status, ['pending'])) {
                    $part = SparePart::where('id', $item->spare_part_id)->lockForUpdate()->firstOrFail();

                    // Check availability
                    if ($part->available_stock < $item->quantity) {
                        throw new Exception("Stok untuk part '{$part->name}' tidak mencukupi (Tersedia: {$part->available_stock}, Diminta: {$item->quantity})");
                    }

                    $part->reserved_quantity += $item->quantity;
                    $part->save();

                    $item->fulfillment_status = 'reserved';
                    $item->save();
                }
            }
        });
    }

    /**
     * Issue/Fulfill parts physically for a Work Order item
     */
    public function issuePartItem(WorkOrderItem $item): void
    {
        DB::transaction(function () use ($item) {
            $item = WorkOrderItem::where('id', $item->id)->lockForUpdate()->firstOrFail();
            if ($item->item_type !== 'part' || ! $item->spare_part_id) {
                throw new Exception('Item bukan merupakan spare part.');
            }

            if ($item->fulfillment_status === 'issued') {
                return; // Already issued
            }

            $part = SparePart::where('id', $item->spare_part_id)->lockForUpdate()->firstOrFail();

            if ($part->stock_quantity < $item->quantity) {
                throw new Exception("Stok fisik part '{$part->name}' tidak mencukupi untuk dikeluarkan!");
            }

            $stockBefore = $part->stock_quantity;
            $part->stock_quantity -= $item->quantity;

            // Release reservation if it was reserved
            if ($item->fulfillment_status === 'reserved') {
                $part->reserved_quantity = max(0, $part->reserved_quantity - $item->quantity);
            }

            $part->save();

            // Record physical stock movement OUT
            StockMovement::create([
                'spare_part_id' => $part->id,
                'user_id' => Auth::id() ?? 1,
                'movement_type' => 'out',
                'quantity' => -$item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $part->stock_quantity,
                'unit_price' => $item->unit_price,
                'reference_type' => 'work_order',
                'reference_id' => $item->work_order_id,
                'reference_number' => $item->workOrder->wo_number ?? null,
                'notes' => "Pemakaian part pada WO {$item->workOrder->wo_number} ({$item->item_name})",
            ]);

            $item->fulfillment_status = 'issued';
            $item->save();

            $this->checkLowStockAlert($part);
        });
    }

    /**
     * Return unused parts from Work Order
     */
    public function returnPartItem(WorkOrderItem $item, float $quantityToReturn, string $reason = ''): void
    {
        DB::transaction(function () use ($item, $quantityToReturn, $reason) {
            $item = WorkOrderItem::where('id', $item->id)->lockForUpdate()->firstOrFail();
            $part = SparePart::where('id', $item->spare_part_id)->lockForUpdate()->firstOrFail();

            if ($item->fulfillment_status === 'issued') {
                $stockBefore = $part->stock_quantity;
                $part->stock_quantity += $quantityToReturn;
                $part->save();

                StockMovement::create([
                    'spare_part_id' => $part->id,
                    'user_id' => Auth::id() ?? 1,
                    'movement_type' => 'return',
                    'quantity' => $quantityToReturn,
                    'stock_before' => $stockBefore,
                    'stock_after' => $part->stock_quantity,
                    'unit_price' => $item->unit_price,
                    'reference_type' => 'work_order_return',
                    'reference_id' => $item->work_order_id,
                    'reference_number' => $item->workOrder->wo_number ?? null,
                    'notes' => "Pengembalian part dari WO {$item->workOrder->wo_number}: {$reason}",
                ]);
            } elseif ($item->fulfillment_status === 'reserved') {
                $part->reserved_quantity = max(0, $part->reserved_quantity - $quantityToReturn);
                $part->save();
            }

            $item->fulfillment_status = 'returned';
            $item->save();
        });
    }

    /**
     * Cancel all reservations on Work Order cancellation
     */
    public function releaseAllReservations(WorkOrder $workOrder): void
    {
        DB::transaction(function () use ($workOrder) {
            foreach ($workOrder->partItems as $item) {
                if ($item->spare_part_id && $item->fulfillment_status === 'reserved') {
                    $part = SparePart::where('id', $item->spare_part_id)->lockForUpdate()->first();
                    if ($part) {
                        $part->reserved_quantity = max(0, $part->reserved_quantity - $item->quantity);
                        $part->save();
                    }
                    $item->fulfillment_status = 'cancelled';
                    $item->save();
                }
            }
        });
    }

    /**
     * Process stock adjustment (Stock Opname)
     */
    public function adjustStock(SparePart $part, float $physicalQty, string $notes): StockMovement
    {
        return DB::transaction(function () use ($part, $physicalQty, $notes) {
            $part = SparePart::where('id', $part->id)->lockForUpdate()->firstOrFail();

            $diff = $physicalQty - (float) $part->stock_quantity;
            $stockBefore = $part->stock_quantity;

            $part->stock_quantity = $physicalQty;
            $part->save();

            $movement = StockMovement::create([
                'spare_part_id' => $part->id,
                'user_id' => Auth::id() ?? 1,
                'movement_type' => 'adjustment',
                'quantity' => $diff,
                'stock_before' => $stockBefore,
                'stock_after' => $physicalQty,
                'unit_price' => $part->reference_cost_price,
                'reference_type' => 'stock_adjustment',
                'reference_id' => $part->id,
                'reference_number' => 'ADJ-'.date('YmdHis'),
                'notes' => $notes ?: 'Penyesuaian stok opname',
            ]);

            AuditService::log(
                'adjusted',
                'Inventory',
                $part->id,
                "Penyesuaian stok spare part {$part->name}: dari {$stockBefore} menjadi {$physicalQty} ({$notes})",
                ['stock_quantity' => $stockBefore],
                ['stock_quantity' => $physicalQty]
            );

            $this->checkLowStockAlert($part);

            return $movement;
        });
    }

    /**
     * Trigger notification if spare part falls below minimum threshold
     */
    public function checkLowStockAlert(SparePart $part): void
    {
        if ($part->isLowStock()) {
            Notification::create([
                'target_role' => 'warehouse',
                'type' => 'low_stock',
                'title' => 'Peringatan Stok Menipis!',
                'message' => "Stok untuk spare part '{$part->name}' ({$part->sku}) tersisa {$part->stock_quantity} {$part->unit} (Batas minimum: {$part->min_stock} {$part->unit}).",
                'url' => route('spare-parts.show', $part->id),
                'created_at' => now(),
            ]);
        }
    }
}
