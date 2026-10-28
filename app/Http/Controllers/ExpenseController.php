<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AuditService;
use App\Services\DocumentNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        protected DocumentNumberService $docService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Expense::with(['category', 'recorder', 'approver']);

        if ($search) {
            $query->where('description', 'like', "%{$search}%")
                ->orWhere('expense_number', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        if ($startDate) {
            $query->whereDate('expense_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('expense_date', '<=', $endDate);
        }

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $totalExpenses = (clone $query)->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'totalExpenses', 'search', 'categoryId', 'startDate', 'endDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'description' => ['required', 'string'],
            'receipt' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
        ], [
            'expense_category_id.required' => 'Kategori pengeluaran wajib dipilih.',
            'amount.required' => 'Nominal pengeluaran wajib diisi.',
            'description.required' => 'Deskripsi keperluan pengeluaran wajib diisi.',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expense_receipts', 'public');
        }

        $expenseNumber = $this->docService->generate('EXP');

        $expense = Expense::create([
            'expense_number' => $expenseNumber,
            'expense_category_id' => $validated['expense_category_id'],
            'recorded_by' => Auth::id() ?? 1,
            'expense_date' => $validated['expense_date'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'description' => $validated['description'],
            'receipt_path' => $receiptPath,
            'status' => 'approved',
            'approved_by' => Auth::id() ?? 1,
        ]);

        AuditService::log('created', 'Expense', $expense->id, "Mencatat pengeluaran {$expense->expense_number} sebesar Rp".number_format($expense->amount, 0, ',', '.')." ({$expense->description})");

        return back()->with('success', 'Pengeluaran operasional berhasil dicatat!');
    }

    public function categories(): View
    {
        $categories = ExpenseCategory::withCount('expenses')->withSum('expenses', 'amount')->orderBy('name')->get();

        return view('expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = true;
        ExpenseCategory::create($validated);

        return back()->with('success', 'Kategori pengeluaran berhasil ditambahkan!');
    }
}
