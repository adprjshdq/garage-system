<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SparePart;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    /**
     * Create a new Purchase Order
     */
    public function createPurchaseOrder(array $data, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $poNumber = $this->docService->generate('PO');

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $data['supplier_id'],
                'created_by' => Auth::id() ?? 1,
                'order_date' => $data['order_date'] ?? now()->toDateString(),
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'ordered', // Or waiting_approval if configured
                'subtotal' => 0,
                'tax_amount' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = 0;
            foreach ($items as $itemData) {
                $lineSubtotal = (float) $itemData['quantity'] * (float) $itemData['unit_cost'];
                $subtotal += $lineSubtotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'spare_part_id' => $itemData['spare_part_id'],
                    'quantity_ordered' => $itemData['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $po->subtotal = $subtotal;
            $po->tax_amount = $taxAmount;
            $po->grand_total = $subtotal + $taxAmount;
            $po->save();

            AuditService::log(
                'created',
                'PurchaseOrder',
                $po->id,
                "Membuat Purchase Order baru: {$po->po_number} senilai Rp".number_format($po->grand_total, 0, ',', '.')
            );

            return $po;
        });
    }

    /**
     * Process Goods Receipt (Penerimaan Barang, partial or full)
     */
    public function processGoodsReceipt(PurchaseOrder $po, array $receiptData, array $receivedItems): GoodsReceipt
    {
        return DB::transaction(function () use ($po, $receiptData, $receivedItems) {
            $grnNumber = $this->docService->generate('GRN');

            $grn = GoodsReceipt::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po->id,
                'received_by' => Auth::id() ?? 1,
                'receipt_date' => $receiptData['receipt_date'] ?? now()->toDateString(),
                'notes' => $receiptData['notes'] ?? null,
            ]);

            $allCompleted = true;

            foreach ($receivedItems as $poItemId => $qtyReceived) {
                $qtyReceived = (float) $qtyReceived;
                if ($qtyReceived <= 0) {
                    continue;
                }

                $poItem = PurchaseOrderItem::where('id', $poItemId)->lockForUpdate()->firstOrFail();
                $part = SparePart::where('id', $poItem->spare_part_id)->lockForUpdate()->firstOrFail();

                // Validate remaining quantity
                $remaining = (float) $poItem->quantity_ordered - (float) $poItem->quantity_received;
                if ($qtyReceived > $remaining) {
                    throw new Exception("Jumlah terima ({$qtyReceived}) melebihi sisa pesanan ({$remaining}) untuk item {$part->name}");
                }

                $poItem->quantity_received += $qtyReceived;
                $poItem->save();

                // Create Goods Receipt Item
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'spare_part_id' => $part->id,
                    'quantity_received' => $qtyReceived,
                    'unit_cost' => $poItem->unit_cost,
                    'subtotal' => $qtyReceived * (float) $poItem->unit_cost,
                ]);

                // Update physical stock and reference cost price
                $stockBefore = $part->stock_quantity;
                $part->stock_quantity += $qtyReceived;
                $part->reference_cost_price = $poItem->unit_cost; // Update latest cost price
                $part->save();

                // Record Stock Movement IN
                StockMovement::create([
                    'spare_part_id' => $part->id,
                    'user_id' => Auth::id() ?? 1,
                    'movement_type' => 'in',
                    'quantity' => $qtyReceived,
                    'stock_before' => $stockBefore,
                    'stock_after' => $part->stock_quantity,
                    'unit_price' => $poItem->unit_cost,
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $grn->id,
                    'reference_number' => $grn->grn_number,
                    'notes' => "Penerimaan barang dari PO {$po->po_number} (GRN: {$grn->grn_number})",
                ]);

                if ($poItem->quantity_received < $poItem->quantity_ordered) {
                    $allCompleted = false;
                }
            }

            // Check if all items in PO are fully received
            foreach ($po->items as $item) {
                if ($item->quantity_received < $item->quantity_ordered) {
                    $allCompleted = false;
                    break;
                }
            }

            $po->status = $allCompleted ? 'fully_received' : 'partially_received';
            $po->save();

            AuditService::log(
                'received',
                'GoodsReceipt',
                $grn->id,
                "Penerimaan Barang {$grn->grn_number} atas PO {$po->po_number} - Status PO: {$po->status}"
            );

            return $grn;
        });
    }

    /**
     * Process Purchase Return (Retur Pembelian ke Supplier)
     */
    public function processReturn(PurchaseOrder $po, array $returnData, array $returnedItems): PurchaseReturn
    {
        return DB::transaction(function () use ($po, $returnData, $returnedItems) {
            $returnNumber = $this->docService->generate('RET');

            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $returnData['goods_receipt_id'] ?? null,
                'processed_by' => Auth::id() ?? 1,
                'return_date' => $returnData['return_date'] ?? now()->toDateString(),
                'reason' => $returnData['reason'],
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($returnedItems as $partId => $data) {
                $qty = (float) ($data['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $part = SparePart::where('id', $partId)->lockForUpdate()->firstOrFail();
                if ($part->stock_quantity < $qty) {
                    throw new Exception("Stok fisik part '{$part->name}' tidak cukup untuk diretur!");
                }

                $cost = (float) ($data['unit_cost'] ?? $part->reference_cost_price);
                $lineSubtotal = $qty * $cost;
                $totalAmount += $lineSubtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'spare_part_id' => $part->id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'subtotal' => $lineSubtotal,
                ]);

                // Reduce stock due to return to supplier
                $stockBefore = $part->stock_quantity;
                $part->stock_quantity -= $qty;
                $part->save();

                StockMovement::create([
                    'spare_part_id' => $part->id,
                    'user_id' => Auth::id() ?? 1,
                    'movement_type' => 'out',
                    'quantity' => -$qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $part->stock_quantity,
                    'unit_price' => $cost,
                    'reference_type' => 'purchase_return',
                    'reference_id' => $return->id,
                    'reference_number' => $return->return_number,
                    'notes' => "Retur pembelian ke supplier: {$returnData['reason']}",
                ]);
            }

            $return->total_amount = $totalAmount;
            $return->save();

            AuditService::log(
                'returned',
                'PurchaseReturn',
                $return->id,
                "Membuat retur pembelian {$return->return_number} senilai Rp".number_format($totalAmount, 0, ',', '.')
            );

            return $return;
        });
    }
}
