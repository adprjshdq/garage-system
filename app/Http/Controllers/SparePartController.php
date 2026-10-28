<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\StockMovement;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparePartController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService,
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $lowStockOnly = $request->boolean('low_stock');

        $query = SparePart::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('rack_location', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStockOnly) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock');
        }

        $spareParts = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = SparePartCategory::orderBy('name')->get();

        return view('spare-parts.index', compact('spareParts', 'categories', 'search', 'categoryId', 'lowStockOnly'));
    }

    public function create(): View
    {
        $categories = SparePartCategory::orderBy('name')->get();

        return view('spare-parts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:spare_parts,sku'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:spare_part_categories,id'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'reference_cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['reserved_quantity'] = 0;
        $validated['is_active'] = true;

        $initialStock = (float) $validated['stock_quantity'];

        $part = SparePart::create($validated);

        // Record initial stock movement if quantity > 0
        if ($initialStock > 0) {
            StockMovement::create([
                'spare_part_id' => $part->id,
                'user_id' => auth()->id() ?? 1,
                'movement_type' => 'initial',
                'quantity' => $initialStock,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'unit_price' => $part->reference_cost_price,
                'reference_type' => 'initial_setup',
                'reference_id' => $part->id,
                'notes' => 'Saldo awal stok spare part',
            ]);
        }

        AuditService::log('created', 'SparePart', $part->id, "Menambahkan spare part baru {$part->name} ({$part->sku}) stok awal: {$initialStock} {$part->unit}");

        return redirect()->route('spare-parts.show', $part->id)->with('success', 'Spare part berhasil ditambahkan!');
    }

    public function show(SparePart $sparePart): View
    {
        $sparePart->load('category');
        $movements = $sparePart->stockMovements()->with('user')->latest()->paginate(15);

        return view('spare-parts.show', compact('sparePart', 'movements'));
    }

    public function edit(SparePart $sparePart): View
    {
        $categories = SparePartCategory::orderBy('name')->get();

        return view('spare-parts.edit', compact('sparePart', 'categories'));
    }

    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:spare_parts,sku,'.$sparePart->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:spare_part_categories,id'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', 'string', 'max:20'],
            'reference_cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        $old = $sparePart->toArray();
        $sparePart->update($validated);

        AuditService::log('updated', 'SparePart', $sparePart->id, "Memperbarui master spare part {$sparePart->name}", $old, $sparePart->toArray());

        return redirect()->route('spare-parts.show', $sparePart->id)->with('success', 'Data spare part berhasil diperbarui!');
    }

    public function adjustStock(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'physical_quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['required', 'string'],
        ], [
            'physical_quantity.required' => 'Jumlah fisik hasil opname wajib diisi.',
            'notes.required' => 'Alasan penyesuaian stok wajib diisi.',
        ]);

        try {
            $this->inventoryService->adjustStock($sparePart, (float) $validated['physical_quantity'], $validated['notes']);

            return back()->with('success', 'Penyesuaian stok (Opname) berhasil dicatat!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyesuaikan stok: '.$e->getMessage());
        }
    }

    public function movements(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $query = StockMovement::with(['sparePart', 'user']);

        if ($search) {
            $query->whereHas('sparePart', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('movement_type', $type);
        }

        $movements = $query->latest()->paginate(20)->withQueryString();

        return view('spare-parts.movements', compact('movements', 'search', 'type'));
    }
}
