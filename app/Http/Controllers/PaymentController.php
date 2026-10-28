<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\ReportingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected ReportingService $reportingService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $method = $request->input('method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Payment::with(['invoice.customer', 'receiver']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($iq) use ($search) {
                        $iq->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        if ($startDate) {
            $query->whereDate('payment_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return view('payments.index', compact('payments', 'search', 'method', 'startDate', 'endDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank_transfer,qris'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.min' => 'Nominal pembayaran minimal Rp1.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        try {
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $payment = $this->paymentService->recordPayment($invoice, $validated);

            return back()->with('success', "Pembayaran {$payment->payment_number} sebesar Rp".number_format($payment->amount, 0, ',', '.').' berhasil dicatat!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pembayaran: '.$e->getMessage());
        }
    }

    public function voidPayment(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
        ], [
            'reason.required' => 'Alasan pembatalan pembayaran wajib diisi.',
        ]);

        try {
            $this->paymentService->voidPayment($payment, $validated['reason']);

            return back()->with('success', 'Pembayaran berhasil dibatalkan dan saldo invoice telah disesuaikan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan pembayaran: '.$e->getMessage());
        }
    }

    public function receivables(): View
    {
        $report = $this->reportingService->getAccountsReceivableReport();

        return view('payments.receivables', [
            'invoices' => $report['invoices'],
            'totalReceivable' => $report['total_receivable'],
            'aging' => $report['aging'],
        ]);
    }
}
