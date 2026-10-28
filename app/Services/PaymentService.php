<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    /**
     * Record payment for an Invoice
     */
    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $invoice = Invoice::where('id', $invoice->id)->lockForUpdate()->firstOrFail();

            if ($invoice->status === 'paid') {
                throw new Exception("Invoice {$invoice->invoice_number} sudah lunas!");
            }

            $amount = (float) $paymentData['amount'];
            if ($amount <= 0) {
                throw new Exception('Nominal pembayaran harus lebih dari 0!');
            }

            if ($amount > (float) $invoice->balance_due) {
                throw new Exception('Nominal pembayaran (Rp'.number_format($amount, 0, ',', '.').') melebihi sisa tagihan (Rp'.number_format($invoice->balance_due, 0, ',', '.').')!');
            }

            $paymentNumber = $this->docService->generate('PAY');

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'], // cash, bank_transfer, qris
                'payment_date' => $paymentData['payment_date'] ?? now()->toDateString(),
                'received_by' => Auth::id() ?? 1,
                'reference_number' => $paymentData['reference_number'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'status' => 'valid',
            ]);

            // Update invoice balances
            $invoice->paid_amount += $amount;
            $invoice->balance_due = max(0, (float) $invoice->grand_total - (float) $invoice->paid_amount);

            if ($invoice->balance_due <= 0) {
                $invoice->status = 'paid';

                // Automatically close the Work Order if fully paid
                if ($invoice->workOrder && $invoice->workOrder->status === 'invoiced') {
                    $invoice->workOrder->status = 'closed';
                    $invoice->workOrder->save();
                }
            } else {
                $invoice->status = 'partially_paid';
            }

            $invoice->save();

            AuditService::log(
                'payment_received',
                'Payment',
                $payment->id,
                "Menerima pembayaran {$payment->payment_number} untuk Invoice {$invoice->invoice_number} sebesar Rp".number_format($amount, 0, ',', '.')." via {$payment->method_label}"
            );

            return $payment;
        });
    }

    /**
     * Void a payment
     */
    public function voidPayment(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'voided') {
                throw new Exception('Pembayaran ini sudah dibatalkan sebelumnya!');
            }

            $invoice = Invoice::where('id', $payment->invoice_id)->lockForUpdate()->firstOrFail();

            $payment->status = 'voided';
            $payment->notes = trim($payment->notes." [DIBATALKAN: {$reason}]");
            $payment->save();

            // Revert invoice balances
            $invoice->paid_amount = max(0, (float) $invoice->paid_amount - (float) $payment->amount);
            $invoice->balance_due = max(0, (float) $invoice->grand_total - (float) $invoice->paid_amount);

            if ($invoice->paid_amount <= 0) {
                $invoice->status = 'unpaid';
            } else {
                $invoice->status = 'partially_paid';
            }

            // If work order was closed, revert it back to invoiced
            if ($invoice->workOrder && $invoice->workOrder->status === 'closed') {
                $invoice->workOrder->status = 'invoiced';
                $invoice->workOrder->save();
            }

            $invoice->save();

            AuditService::log(
                'voided',
                'Payment',
                $payment->id,
                "Membatalkan pembayaran {$payment->payment_number} (Rp".number_format($payment->amount, 0, ',', '.')."): {$reason}"
            );
        });
    }
}
