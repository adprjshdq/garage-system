<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Mechanic;
use App\Models\ServiceCatalog;
use App\Models\ServiceReception;
use App\Models\SparePart;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Services\InventoryService;
use App\Services\WorkOrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(
        protected WorkOrderService $woService,
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $mechanicId = $request->input('mechanic_id');

        $query = WorkOrder::with(['customer', 'vehicle', 'mechanics', 'invoice']);

        // Mechanic role filter: if logged in as mechanic, show only assigned jobs
        if (auth()->user()->hasRole('mechanic') && ! auth()->user()->isAdmin() && ! auth()->user()->hasRole('service_advisor')) {
            $mechanic = auth()->user()->mechanic;
            if ($mechanic) {
                $query->whereHas('mechanics', fn ($q) => $q->where('mechanic_id', $mechanic->id));
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', fn ($vq) => $vq->where('license_plate', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($mechanicId) {
            $query->whereHas('mechanics', fn ($q) => $q->where('mechanic_id', $mechanicId));
        }

        $workOrders = $query->latest('created_at')->paginate(15)->withQueryString();
        $mechanics = Mechanic::where('is_active', true)->orderBy('name')->get();

        return view('work-orders.index', compact('workOrders', 'mechanics', 'search', 'status', 'mechanicId'));
    }

    public function create(Request $request): View
    {
        $receptionId = $request->input('reception_id');
        $reception = $receptionId ? ServiceReception::with(['customer', 'vehicle'])->find($receptionId) : null;

        $customers = Customer::where('is_active', true)->with('vehicles')->orderBy('name')->get();
        $services = ServiceCatalog::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $spareParts = SparePart::where('is_active', true)->orderBy('name')->get();

        return view('work-orders.create', compact('reception', 'customers', 'services', 'spareParts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'reception_id' => ['nullable', 'exists:service_receptions,id'],
            'mileage' => ['required', 'integer', 'min:0'],
            'customer_complaint' => ['required', 'string'],
            'inspection_result' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,part'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.service_id' => ['nullable', 'exists:service_catalog,id'],
            'items.*.spare_part_id' => ['nullable', 'exists:spare_parts,id'],
        ], [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'items.required' => 'Minimal harus menambahkan 1 pekerjaan atau spare part ke Work Order.',
        ]);

        try {
            $wo = $this->woService->createWorkOrder($validated, $validated['items']);

            return redirect()->route('work-orders.show', $wo->id)->with('success', 'Work Order berhasil dibuat dalam status Draft!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat Work Order: '.$e->getMessage());
        }
    }

    public function show(WorkOrder $workOrder): View
    {
        $workOrder->load([
            'customer',
            'vehicle',
            'reception',
            'creator',
            'items.service',
            'items.sparePart',
            'mechanics',
            'approvals.recorder',
            'inspections.inspector',
            'invoice.payments',
        ]);

        $availableMechanics = Mechanic::where('is_active', true)->orderBy('name')->get();

        return view('work-orders.show', compact('workOrder', 'availableMechanics'));
    }

    public function approve(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'approval_method' => ['required', 'in:in_person,phone_wa,digital_signature'],
            'signature_data' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'approval_method.required' => 'Pilih metode persetujuan pelanggan.',
        ]);

        try {
            $this->woService->recordApproval($workOrder, $validated);

            return back()->with('success', 'Persetujuan pelanggan berhasil dicatat! Status WO berubah menjadi Disetujui.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses persetujuan: '.$e->getMessage());
        }
    }

    public function startWork(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'mechanic_ids' => ['required', 'array', 'min:1'],
            'mechanic_ids.*' => ['exists:mechanics,id'],
        ], [
            'mechanic_ids.required' => 'Pilih minimal satu mekanik yang bertugas.',
        ]);

        try {
            $this->woService->startWork($workOrder, $validated['mechanic_ids']);

            return back()->with('success', 'Pengerjaan Work Order berhasil dimulai!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memulai pengerjaan: '.$e->getMessage());
        }
    }

    public function issuePart(WorkOrder $workOrder, WorkOrderItem $item): RedirectResponse
    {
        try {
            $this->inventoryService->issuePartItem($item);

            return back()->with('success', "Spare part '{$item->item_name}' berhasil dikeluarkan dan dipotong dari stok fisik!");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengeluarkan spare part: '.$e->getMessage());
        }
    }

    public function inspect(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'brake_check' => ['required', 'in:ok,need_attention,bad'],
            'engine_check' => ['required', 'in:ok,need_attention,bad'],
            'electric_check' => ['required', 'in:ok,need_attention,bad'],
            'tires_check' => ['required', 'in:ok,need_attention,bad'],
            'test_drive_notes' => ['nullable', 'string'],
            'is_passed' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $inspection = $this->woService->submitInspection($workOrder, $validated);
            $msg = $inspection->is_passed ? 'Pemeriksaan akhir LULUS! Work Order siap ditagihkan.' : 'Pemeriksaan akhir dicatat, kendaraan membutuhkan pengerjaan tambahan.';

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pemeriksaan: '.$e->getMessage());
        }
    }

    public function cancel(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string'],
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        try {
            $this->woService->cancelWorkOrder($workOrder, $validated['cancellation_reason']);

            return back()->with('success', 'Work Order berhasil dibatalkan dan alokasi stok telah dikembalikan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan Work Order: '.$e->getMessage());
        }
    }

    public function print(WorkOrder $workOrder): View
    {
        $workOrder->load(['customer', 'vehicle', 'items', 'mechanics', 'latestApproval']);

        return view('work-orders.print', compact('workOrder'));
    }
}
