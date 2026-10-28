<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\WorkOrder;
use App\Models\WorkshopSetting;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    /**
     * Create an Invoice from a completed Work Order
     */
    public function createFromWorkOrder(WorkOrder $wo, array $options = []): Invoice
    {
        return DB::transaction(function () use ($wo, $options) {
            $wo = WorkOrder::where('id', $wo->id)->lockForUpdate()->firstOrFail();

            if (! in_array($wo->status, ['completed', 'final_inspection'])) {
                throw new Exception("Work Order belum memenuhi syarat untuk ditagihkan (Status harus 'Selesai')!");
            }

            if ($wo->invoice) {
                throw new Exception("Invoice untuk Work Order {$wo->wo_number} sudah pernah diterbitkan ({$wo->invoice->invoice_number})!");
            }

            $invoiceNumber = $this->docService->generate('INV');

            $subtotalServices = 0;
            $subtotalParts = 0;

            foreach ($wo->items as $item) {
                if ($item->item_type === 'service') {
                    $subtotalServices += (float) $item->subtotal;
                } else {
                    $subtotalParts += (float) $item->subtotal;
                }
            }

            $subtotal = $subtotalServices + $subtotalParts;
            $discount = (float) ($options['discount'] ?? 0);
            $taxPercentage = (float) ($options['tax_percentage'] ?? WorkshopSetting::get('default_tax_rate', '0'));

            $afterDiscount = max(0, $subtotal - $discount);
            $taxAmount = ($afterDiscount * $taxPercentage) / 100;
            $grandTotal = $afterDiscount + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'work_order_id' => $wo->id,
                'customer_id' => $wo->customer_id,
                'invoice_date' => $options['invoice_date'] ?? now()->toDateString(),
                'due_date' => $options['due_date'] ?? now()->toDateString(),
                'subtotal_services' => $subtotalServices,
                'subtotal_parts' => $subtotalParts,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'paid_amount' => 0,
                'balance_due' => $grandTotal,
                'status' => 'unpaid',
                'notes' => $options['notes'] ?? null,
                'created_by' => Auth::id() ?? 1,
            ]);

            // Snapshot items
            foreach ($wo->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item->item_type,
                    'reference_id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $wo->status = 'invoiced';
            $wo->save();

            AuditService::log(
                'created',
                'Invoice',
                $invoice->id,
                "Menerbitkan Invoice {$invoice->invoice_number} dari WO {$wo->wo_number} sebesar Rp".number_format($invoice->grand_total, 0, ',', '.')
            );

            return $invoice;
        });
    }
}
