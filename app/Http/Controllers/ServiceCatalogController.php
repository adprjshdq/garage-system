<?php

namespace App\Http\Controllers;

use App\Models\ServiceCatalog;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $category = $request->input('category');

        $query = ServiceCatalog::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $services = $query->orderBy('category')->orderBy('name')->paginate(15)->withQueryString();
        $categories = ServiceCatalog::select('category')->distinct()->pluck('category');

        return view('service-catalog.index', compact('services', 'categories', 'search', 'category'));
    }

    public function create(): View
    {
        $categories = ServiceCatalog::select('category')->distinct()->pluck('category');

        return view('service-catalog.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:service_catalog,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:5'],
        ]);

        $validated['is_active'] = true;
        $service = ServiceCatalog::create($validated);

        AuditService::log('created', 'ServiceCatalog', $service->id, "Menambahkan jasa servis baru {$service->name} ({$service->code}) - Rp".number_format($service->price, 0, ',', '.'));

        return redirect()->route('services.index')->with('success', 'Jasa servis berhasil ditambahkan!');
    }

    public function edit(ServiceCatalog $service): View
    {
        $categories = ServiceCatalog::select('category')->distinct()->pluck('category');

        return view('service-catalog.edit', compact('service', 'categories'));
    }

    public function update(Request $request, ServiceCatalog $service): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:service_catalog,code,'.$service->id],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:5'],
            'is_active' => ['required', 'boolean'],
        ]);

        $old = $service->toArray();
        $service->update($validated);

        AuditService::log('updated', 'ServiceCatalog', $service->id, "Memperbarui jasa servis {$service->name}", $old, $service->toArray());

        return redirect()->route('services.index')->with('success', 'Jasa servis berhasil diperbarui!');
    }

    public function destroy(ServiceCatalog $service): RedirectResponse
    {
        $service->delete();
        AuditService::log('deleted', 'ServiceCatalog', $service->id, "Menghapus jasa servis {$service->name}");

        return redirect()->route('services.index')->with('success', 'Jasa servis berhasil dihapus!');
    }
}
