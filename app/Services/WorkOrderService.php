<?php

namespace App\Services;

use App\Models\ServiceReception;
use App\Models\WorkOrder;
use App\Models\WorkOrderApproval;
use App\Models\WorkOrderInspection;
use App\Models\WorkOrderItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function __construct(
        protected DocumentNumberService $docService,
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create a new Work Order
     */
    public function createWorkOrder(array $data, array $items = []): WorkOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $woNumber = $this->docService->generate('WO');

            $wo = WorkOrder::create([
                'wo_number' => $woNumber,
                'reception_id' => $data['reception_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'vehicle_id' => $data['vehicle_id'],
                'mileage' => $data['mileage'],
                'customer_complaint' => $data['customer_complaint'],
                'inspection_result' => $data['inspection_result'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id() ?? 1,
            ]);

            // If created from reception, update reception status to 'processed'
            if (! empty($data['reception_id'])) {
                $reception = ServiceReception::find($data['reception_id']);
                if ($reception) {
                    $reception->status = 'processed';
                    $reception->save();
                }
            }

            // Sync items
            $this->syncItems($wo, $items);

            AuditService::log('created', 'WorkOrder', $wo->id, "Membuat Work Order baru {$wo->wo_number}");

            return $wo;
        });
    }

    /**
     * Sync items and recalculate costs
     */
    public function syncItems(WorkOrder $wo, array $items): void
    {
        $totalEstimated = 0;

        // Existing items can be replaced if in draft
        if (in_array($wo->status, ['draft', 'waiting_approval'])) {
            $wo->items()->delete();

            foreach ($items as $itemData) {
                $qty = (float) ($itemData['quantity'] ?? 1);
                $price = (float) ($itemData['unit_price'] ?? 0);
                $discount = (float) ($itemData['discount'] ?? 0);
                $subtotal = ($qty * $price) - $discount;
                $totalEstimated += $subtotal;

                WorkOrderItem::create([
                    'work_order_id' => $wo->id,
                    'item_type' => $itemData['item_type'], // service or part
                    'service_id' => $itemData['item_type'] === 'service' ? ($itemData['service_id'] ?? null) : null,
                    'spare_part_id' => $itemData['item_type'] === 'part' ? ($itemData['spare_part_id'] ?? null) : null,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'subtotal' => $subtotal,
                    'is_approved' => true,
                    'fulfillment_status' => 'pending',
                ]);
            }

            $wo->estimated_cost = $totalEstimated;
            $wo->actual_total_cost = $totalEstimated;
            $wo->save();
        }
    }

    /**
     * Record Customer Approval
     */
    public function recordApproval(WorkOrder $wo, array $approvalData): WorkOrderApproval
    {
        return DB::transaction(function () use ($wo, $approvalData) {
            $version = $wo->approvals()->count() + 1;

            $approval = WorkOrderApproval::create([
                'work_order_id' => $wo->id,
                'version' => $version,
                'estimated_amount' => $wo->estimated_cost,
                'approved_at' => now(),
                'approval_method' => $approvalData['approval_method'], // in_person, phone_wa, digital_signature
                'recorded_by' => Auth::id() ?? 1,
                'signature_data' => $approvalData['signature_data'] ?? null,
                'notes' => $approvalData['notes'] ?? null,
            ]);

            // Transition status to approved
            $wo->status = 'approved';
            $wo->save();

            // Reserve spare parts stock
            $this->inventoryService->reserveStockForWorkOrder($wo);

            AuditService::log(
                'approved',
                'WorkOrder',
                $wo->id,
                "Persetujuan estimasi pelanggan WO {$wo->wo_number} versi {$version} metode {$approval->method_label}"
            );

            return $approval;
        });
    }

    /**
     * Start Work Order execution
     */
    public function startWork(WorkOrder $wo, array $mechanicIds = []): void
    {
        DB::transaction(function () use ($wo, $mechanicIds) {
            if (! in_array($wo->status, ['approved', 'waiting_parts'])) {
                throw new Exception("Work Order harus dalam status 'Disetujui' untuk dapat dimulai!");
            }

            $wo->status = 'in_progress';
            $wo->start_time = $wo->start_time ?? now();
            $wo->save();

            if (! empty($mechanicIds)) {
                $syncData = [];
                foreach ($mechanicIds as $id) {
                    $syncData[$id] = [
                        'started_at' => now(),
                        'role_description' => 'Mekanik Bertugas',
                    ];
                }
                $wo->mechanics()->syncWithoutDetaching($syncData);
            }

            AuditService::log('status_changed', 'WorkOrder', $wo->id, "Pengerjaan WO {$wo->wo_number} dimulai (Status: Sedang Dikerjakan)");
        });
    }

    /**
     * Complete Inspection (Pemeriksaan Akhir)
     */
    public function submitInspection(WorkOrder $wo, array $inspectionData): WorkOrderInspection
    {
        return DB::transaction(function () use ($wo, $inspectionData) {
            $inspection = WorkOrderInspection::create([
                'work_order_id' => $wo->id,
                'inspector_id' => Auth::id() ?? 1,
                'inspected_at' => now(),
                'brake_check' => $inspectionData['brake_check'] ?? 'ok',
                'engine_check' => $inspectionData['engine_check'] ?? 'ok',
                'electric_check' => $inspectionData['electric_check'] ?? 'ok',
                'tires_check' => $inspectionData['tires_check'] ?? 'ok',
                'test_drive_notes' => $inspectionData['test_drive_notes'] ?? null,
                'is_passed' => (bool) ($inspectionData['is_passed'] ?? true),
                'notes' => $inspectionData['notes'] ?? null,
            ]);

            if ($inspection->is_passed) {
                $wo->status = 'completed';
                $wo->completion_time = now();

                // Recalculate actual final total cost from items
                $wo->actual_total_cost = $wo->items()->sum('subtotal');
                $wo->save();

                // Mark vehicle last mileage
                $vehicle = $wo->vehicle;
                if ($vehicle && $wo->mileage > $vehicle->last_mileage) {
                    $vehicle->last_mileage = $wo->mileage;
                    $vehicle->save();
                }

                // If from reception, mark reception completed
                if ($wo->reception) {
                    $wo->reception->status = 'completed';
                    $wo->reception->save();
                }
            } else {
                $wo->status = 'in_progress';
                $wo->save();
            }

            AuditService::log(
                'inspected',
                'WorkOrder',
                $wo->id,
                "Pemeriksaan akhir WO {$wo->wo_number} selesai. Hasil: ".($inspection->is_passed ? 'LULUS (Selesai)' : 'BELUM LULUS')
            );

            return $inspection;
        });
    }

    /**
     * Cancel Work Order
     */
    public function cancelWorkOrder(WorkOrder $wo, string $reason): void
    {
        DB::transaction(function () use ($wo, $reason) {
            if (in_array($wo->status, ['invoiced', 'closed'])) {
                throw new Exception('Work Order yang sudah ditagihkan atau ditutup tidak dapat dibatalkan!');
            }

            // Release any reserved stock
            $this->inventoryService->releaseAllReservations($wo);

            $wo->status = 'cancelled';
            $wo->cancellation_reason = $reason;
            $wo->save();

            AuditService::log(
                'cancelled',
                'WorkOrder',
                $wo->id,
                "Membatalkan Work Order {$wo->wo_number}: {$reason}"
            );
        });
    }
}
