<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $query = Supplier::withCount('purchaseOrders');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        $validated['code'] = $this->docService->generate('SUPP');
        $validated['is_active'] = true;

        $supplier = Supplier::create($validated);

        AuditService::log('created', 'Supplier', $supplier->id, "Menambahkan data supplier baru {$supplier->name} ({$supplier->code})");

        return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchaseOrders' => fn ($q) => $q->latest()->take(10)]);
        $totalOrders = $supplier->purchaseOrders()->count();
        $totalPurchased = $supplier->purchaseOrders()->whereIn('status', ['ordered', 'partially_received', 'fully_received'])->sum('grand_total');

        return view('suppliers.show', compact('supplier', 'totalOrders', 'totalPurchased'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $old = $supplier->toArray();
        $supplier->update($validated);

        AuditService::log('updated', 'Supplier', $supplier->id, "Memperbarui data supplier {$supplier->name}", $old, $supplier->toArray());

        return redirect()->route('suppliers.show', $supplier->id)->with('success', 'Data supplier berhasil diperbarui!');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier ini memiliki riwayat Purchase Order dan tidak dapat dihapus. Anda dapat mengubah statusnya menjadi nonaktif.');
        }

        $supplier->delete();
        AuditService::log('deleted', 'Supplier', $supplier->id, "Menghapus supplier {$supplier->name}");

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus!');
    }
}
