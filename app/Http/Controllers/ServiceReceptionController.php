<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceReception;
use App\Models\Vehicle;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceReceptionController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = ServiceReception::with(['customer', 'vehicle', 'receiver', 'workOrder']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reception_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', fn ($vq) => $vq->where('license_plate', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $receptions = $query->latest('reception_time')->paginate(15)->withQueryString();

        return view('receptions.index', compact('receptions', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('is_active', true)->with('vehicles')->orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');

        return view('receptions.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'mileage_in' => ['required', 'integer', 'min:0'],
            'customer_complaint' => ['required', 'string'],
            'initial_condition' => ['nullable', 'string'],
            'accessories_note' => ['nullable', 'string'],
            'estimated_completion_time' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
        ], [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'vehicle_id.required' => 'Kendaraan wajib dipilih.',
            'mileage_in.required' => 'Kilometer kendaraan saat masuk wajib diisi.',
            'customer_complaint.required' => 'Keluhan pelanggan wajib diisi.',
        ]);

        // Validate vehicle belongs to customer
        $vehicle = Vehicle::where('id', $validated['vehicle_id'])
            ->where('customer_id', $validated['customer_id'])
            ->first();

        if (! $vehicle) {
            return back()->withInput()->withErrors(['vehicle_id' => 'Kendaraan yang dipilih tidak sesuai dengan pelanggan tersebut!']);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reception_photos', 'public');
        }

        $receptionNumber = $this->docService->generate('REC');

        $reception = ServiceReception::create([
            'reception_number' => $receptionNumber,
            'customer_id' => $validated['customer_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'receiver_id' => Auth::id() ?? 1,
            'mileage_in' => $validated['mileage_in'],
            'customer_complaint' => $validated['customer_complaint'],
            'initial_condition' => $validated['initial_condition'] ?? null,
            'accessories_note' => $validated['accessories_note'] ?? null,
            'photo_path' => $photoPath,
            'reception_time' => now(),
            'estimated_completion_time' => $validated['estimated_completion_time'] ?? null,
            'status' => 'waiting_inspection',
        ]);

        // Update vehicle last mileage
        if ($validated['mileage_in'] > $vehicle->last_mileage) {
            $vehicle->last_mileage = $validated['mileage_in'];
            $vehicle->save();
        }

        AuditService::log('created', 'ServiceReception', $reception->id, "Penerimaan kendaraan {$reception->reception_number} ({$vehicle->license_plate})");

        return redirect()->route('receptions.show', $reception->id)->with('success', 'Penerimaan kendaraan berhasil dicatat!');
    }

    public function show(ServiceReception $reception): View
    {
        $reception->load(['customer', 'vehicle', 'receiver', 'workOrder.mechanics']);

        return view('receptions.show', compact('reception'));
    }

    public function print(ServiceReception $reception): View
    {
        $reception->load(['customer', 'vehicle', 'receiver']);

        return view('receptions.print', compact('reception'));
    }
}
