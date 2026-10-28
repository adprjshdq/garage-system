<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Models\WorkshopSetting;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Invoice::with(['customer', 'workOrder.vehicle', 'payments']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('workOrder', fn ($wq) => $wq->where('wo_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('invoice_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('invoice_date', '<=', $endDate);
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices', 'search', 'status', 'startDate', 'endDate'));
    }

    public function create(Request $request): View
    {
        $woId = $request->input('work_order_id');
        $selectedWo = $woId ? WorkOrder::with(['customer', 'vehicle', 'items'])->find($woId) : null;

        $completedWorkOrders = WorkOrder::with(['customer', 'vehicle'])
            ->whereIn('status', ['completed', 'final_inspection'])
            ->whereDoesntHave('invoice')
            ->latest()
            ->get();

        $defaultTaxRate = (float) WorkshopSetting::get('default_tax_rate', '0');

        return view('invoices.create', compact('completedWorkOrders', 'selectedWo', 'defaultTaxRate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_order_id' => ['required', 'exists:work_orders,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'work_order_id.required' => 'Work Order yang akan ditagihkan wajib dipilih.',
            'due_date.after_or_equal' => 'Jatuh tempo tidak boleh sebelum tanggal invoice.',
        ]);

        try {
            $wo = WorkOrder::findOrFail($validated['work_order_id']);
            $invoice = $this->invoiceService->createFromWorkOrder($wo, $validated);

            return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice berhasil diterbitkan!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menerbitkan invoice: '.$e->getMessage());
        }
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'customer',
            'workOrder.vehicle',
            'workOrder.mechanics',
            'items',
            'payments.receiver',
            'creator',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Thermal POS Print (58mm/80mm Receipt)
     */
    public function printThermal(Invoice $invoice): View
    {
        $invoice->load(['customer', 'workOrder.vehicle', 'items', 'payments']);
        $workshopName = WorkshopSetting::get('workshop_name', 'MotoCare Workshop');
        $workshopAddress = WorkshopSetting::get('workshop_address', 'Jl. Bengkel Motor No. 1');
        $workshopPhone = WorkshopSetting::get('workshop_phone', '0812-3456-7890');

        return view('invoices.print-thermal', compact('invoice', 'workshopName', 'workshopAddress', 'workshopPhone'));
    }

    /**
     * Formal A4 Print / PDF Download
     */
    public function printFormal(Invoice $invoice): View
    {
        $invoice->load(['customer', 'workOrder.vehicle', 'items', 'payments', 'creator']);
        $workshopName = WorkshopSetting::get('workshop_name', 'MotoCare Workshop');
        $workshopAddress = WorkshopSetting::get('workshop_address', 'Jl. Bengkel Motor No. 1');
        $workshopPhone = WorkshopSetting::get('workshop_phone', '0812-3456-7890');
        $workshopEmail = WorkshopSetting::get('workshop_email', 'info@motocare.com');

        return view('invoices.print-formal', compact('invoice', 'workshopName', 'workshopAddress', 'workshopPhone', 'workshopEmail'));
    }

    /**
     * Download formal PDF
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'workOrder.vehicle', 'items', 'payments', 'creator']);
        $workshopName = WorkshopSetting::get('workshop_name', 'MotoCare Workshop');
        $workshopAddress = WorkshopSetting::get('workshop_address', 'Jl. Bengkel Motor No. 1');
        $workshopPhone = WorkshopSetting::get('workshop_phone', '0812-3456-7890');
        $workshopEmail = WorkshopSetting::get('workshop_email', 'info@motocare.com');

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'workshopName', 'workshopAddress', 'workshopPhone', 'workshopEmail'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }
}
