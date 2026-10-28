<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $brand = $request->input('brand');

        $query = Vehicle::with('customer')->withCount('workOrders');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('license_plate', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('vin_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($brand) {
            $query->where('brand', $brand);
        }

        $vehicles = $query->latest()->paginate(15)->withQueryString();
        $brands = Vehicle::select('brand')->distinct()->pluck('brand');

        return view('vehicles.index', compact('vehicles', 'brands', 'search', 'brand'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $selectedCustomerId = $request->input('customer_id');

        return view('vehicles.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'license_plate' => ['required', 'string', 'max:20'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'vin_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'last_mileage' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'customer_id.required' => 'Pelanggan pemilik kendaraan wajib dipilih.',
            'license_plate.required' => 'Nomor polisi kendaraan wajib diisi.',
            'brand.required' => 'Merek kendaraan wajib diisi.',
            'model.required' => 'Model / Tipe kendaraan wajib diisi.',
        ]);

        $validated['license_plate'] = strtoupper(trim($validated['license_plate']));
        $validated['last_mileage'] = $validated['last_mileage'] ?? 0;
        $validated['is_active'] = true;

        $vehicle = Vehicle::create($validated);

        AuditService::log('created', 'Vehicle', $vehicle->id, "Menambahkan kendaraan {$vehicle->license_plate} ({$vehicle->brand} {$vehicle->model}) milik {$vehicle->customer->name}");

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', 'Kendaraan berhasil didaftarkan!');
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'customer',
            'workOrders' => fn ($q) => $q->with('mechanics')->latest()->take(15),
            'receptions' => fn ($q) => $q->latest()->take(10),
        ]);

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.edit', compact('vehicle', 'customers'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'license_plate' => ['required', 'string', 'max:20'],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'vin_number' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'last_mileage' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['license_plate'] = strtoupper(trim($validated['license_plate']));

        $old = $vehicle->toArray();
        $vehicle->update($validated);

        AuditService::log('updated', 'Vehicle', $vehicle->id, "Memperbarui data kendaraan {$vehicle->license_plate}", $old, $vehicle->toArray());

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', 'Data kendaraan berhasil diperbarui!');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->workOrders()->exists() || $vehicle->receptions()->exists()) {
            return back()->with('error', 'Kendaraan ini memiliki riwayat servis dan tidak dapat dihapus permanen. Ubah status menjadi nonaktif.');
        }

        $vehicle->delete();
        AuditService::log('deleted', 'Vehicle', $vehicle->id, "Menghapus kendaraan {$vehicle->license_plate}");

        return redirect()->route('vehicles.index')->with('success', 'Kendaraan berhasil dihapus!');
    }
}
