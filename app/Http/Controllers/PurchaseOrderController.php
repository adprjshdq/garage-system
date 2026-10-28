<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\SparePart;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = PurchaseOrder::with(['supplier', 'creator', 'items.sparePart']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->latest('order_date')->paginate(15)->withQueryString();

        return view('purchases.index', compact('purchaseOrders', 'search', 'status'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'spareParts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.spare_part_id' => ['required', 'exists:spare_parts,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'items.required' => 'Tambahkan minimal satu spare part untuk dipesan.',
        ]);

        try {
            $po = $this->purchaseService->createPurchaseOrder($validated, $validated['items']);

            return redirect()->route('purchases.show', $po->id)->with('success', 'Purchase Order berhasil dibuat!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal membuat Purchase Order: '.$e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load([
            'supplier',
            'creator',
            'items.sparePart',
            'goodsReceipts.items.sparePart',
            'returns.items.sparePart',
        ]);

        return view('purchases.show', ['po' => $purchase]);
    }

    public function receiveGoods(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'received_qty' => ['required', 'array'],
        ]);

        try {
            $this->purchaseService->processGoodsReceipt($purchase, [
                'receipt_date' => $validated['receipt_date'],
                'notes' => $validated['notes'] ?? null,
            ], $validated['received_qty']);

            return back()->with('success', 'Penerimaan barang berhasil diposting dan stok spare part telah bertambah!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penerimaan: '.$e->getMessage());
        }
    }

    public function returnGoods(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'return_items' => ['required', 'array'],
        ]);

        try {
            $this->purchaseService->processReturn($purchase, [
                'return_date' => $validated['return_date'],
                'reason' => $validated['reason'],
            ], $validated['return_items']);

            return back()->with('success', 'Retur pembelian berhasil diproses!');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses retur: '.$e->getMessage());
        }
    }

    public function print(PurchaseOrder $purchase): View
    {
        $purchase->load(['supplier', 'items.sparePart', 'creator']);

        return view('purchases.print', ['po' => $purchase]);
    }
}
