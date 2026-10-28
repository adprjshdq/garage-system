<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Customer::withCount(['vehicles', 'workOrders']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $customers = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers', 'search', 'status'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama pelanggan wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
        ]);

        $validated['customer_code'] = $this->docService->generate('CUST');
        $validated['is_active'] = true;

        $customer = Customer::create($validated);

        AuditService::log('created', 'Customer', $customer->id, "Menambahkan pelanggan baru {$customer->name} ({$customer->customer_code})");

        return redirect()->route('customers.show', $customer->id)->with('success', 'Pelanggan berhasil ditambahkan!');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'vehicles',
            'workOrders' => fn ($q) => $q->latest()->take(10),
            'invoices' => fn ($q) => $q->latest()->take(10),
        ]);

        $totalSpent = $customer->invoices()->where('status', '!=', 'cancelled')->sum('paid_amount');
        $totalOutstanding = $customer->invoices()->whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_due');

        return view('customers.show', compact('customer', 'totalSpent', 'totalOutstanding'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $old = $customer->toArray();
        $customer->update($validated);

        AuditService::log('updated', 'Customer', $customer->id, "Memperbarui data pelanggan {$customer->name}", $old, $customer->toArray());

        return redirect()->route('customers.show', $customer->id)->with('success', 'Data pelanggan berhasil diperbarui!');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->workOrders()->exists() || $customer->invoices()->exists()) {
            return back()->with('error', 'Pelanggan ini memiliki riwayat transaksi aktif dan tidak dapat dihapus. Anda dapat mengubah statusnya menjadi Nonaktif.');
        }

        $customer->delete();
        AuditService::log('deleted', 'Customer', $customer->id, "Menghapus pelanggan {$customer->name}");

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus!');
    }
}
