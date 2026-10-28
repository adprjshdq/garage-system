<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SparePart;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;

class ReportingService
{
    /**
     * Service Operations Report
     */
    public function getServiceReport(?string $startDate = null, ?string $endDate = null, ?string $status = null, ?int $mechanicId = null): array
    {
        $query = WorkOrder::with(['customer', 'vehicle', 'mechanics']);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($mechanicId) {
            $query->whereHas('mechanics', function ($q) use ($mechanicId) {
                $q->where('mechanic_id', $mechanicId);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_orders' => $orders->count(),
            'completed' => $orders->whereIn('status', ['completed', 'invoiced', 'closed'])->count(),
            'in_progress' => $orders->where('status', 'in_progress')->count(),
            'pending' => $orders->whereIn('status', ['draft', 'waiting_approval', 'approved', 'waiting_parts'])->count(),
            'cancelled' => $orders->where('status', 'cancelled')->count(),
            'total_revenue' => $orders->whereIn('status', ['completed', 'invoiced', 'closed'])->sum('actual_total_cost'),
        ];

        return [
            'data' => $orders,
            'summary' => $summary,
        ];
    }

    /**
     * Financial & Revenue Report (Invoice Basis vs Payment Basis)
     */
    public function getRevenueReport(?string $startDate = null, ?string $endDate = null): array
    {
        // 1. Invoice Basis (Tagihan yang diterbitkan)
        $invoiceQuery = Invoice::where('status', '!=', 'cancelled');
        if ($startDate) {
            $invoiceQuery->whereDate('invoice_date', '>=', $startDate);
        }
        if ($endDate) {
            $invoiceQuery->whereDate('invoice_date', '<=', $endDate);
        }

        $invoices = $invoiceQuery->get();
        $invoicedServices = $invoices->sum('subtotal_services');
        $invoicedParts = $invoices->sum('subtotal_parts');
        $invoicedDiscount = $invoices->sum('discount');
        $invoicedTax = $invoices->sum('tax_amount');
        $totalInvoiced = $invoices->sum('grand_total');

        // 2. Payment Basis (Uang kas aktual yang masuk)
        $paymentQuery = Payment::where('status', 'valid');
        if ($startDate) {
            $paymentQuery->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $paymentQuery->whereDate('payment_date', '<=', $endDate);
        }

        $payments = $paymentQuery->get();
        $totalCashReceived = $payments->where('payment_method', 'cash')->sum('amount');
        $totalBankReceived = $payments->where('payment_method', 'bank_transfer')->sum('amount');
        $totalQrisReceived = $payments->where('payment_method', 'qris')->sum('amount');
        $totalActualPayments = $payments->sum('amount');

        // 3. Operational Expenses
        $expenseQuery = Expense::where('status', 'approved');
        if ($startDate) {
            $expenseQuery->whereDate('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $expenseQuery->whereDate('expense_date', '<=', $endDate);
        }

        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        return [
            'invoiced_basis' => [
                'services' => $invoicedServices,
                'parts' => $invoicedParts,
                'discount' => $invoicedDiscount,
                'tax' => $invoicedTax,
                'total_invoiced' => $totalInvoiced,
                'count' => $invoices->count(),
            ],
            'cash_basis' => [
                'cash' => $totalCashReceived,
                'bank' => $totalBankReceived,
                'qris' => $totalQrisReceived,
                'total_received' => $totalActualPayments,
                'count' => $payments->count(),
            ],
            'expenses' => [
                'total_expenses' => $totalExpenses,
                'count' => $expenses->count(),
            ],
            'net_cash_flow' => $totalActualPayments - $totalExpenses,
        ];
    }

    /**
     * Accounts Receivable Report (Piutang Pelanggan)
     */
    public function getAccountsReceivableReport(): array
    {
        $invoices = Invoice::with(['customer', 'workOrder'])
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date', 'asc')
            ->get();

        $aging = [
            'current' => 0,      // Belum jatuh tempo
            '1_30_days' => 0,    // Terlambat 1 - 30 hari
            '31_60_days' => 0,   // Terlambat 31 - 60 hari
            'over_60_days' => 0, // Terlambat > 60 hari
        ];

        $today = Carbon::today();

        foreach ($invoices as $inv) {
            $dueDate = Carbon::parse($inv->due_date);
            $balance = (float) $inv->balance_due;

            if ($dueDate->isFuture() || $dueDate->isToday()) {
                $aging['current'] += $balance;
            } else {
                $daysOverdue = $dueDate->diffInDays($today);
                if ($daysOverdue <= 30) {
                    $aging['1_30_days'] += $balance;
                } elseif ($daysOverdue <= 60) {
                    $aging['31_60_days'] += $balance;
                } else {
                    $aging['over_60_days'] += $balance;
                }
            }
        }

        return [
            'invoices' => $invoices,
            'total_receivable' => $invoices->sum('balance_due'),
            'aging' => $aging,
        ];
    }

    /**
     * Inventory Valuation and Movement Report
     */
    public function getInventoryReport(): array
    {
        $parts = SparePart::with('category')->orderBy('name', 'asc')->get();

        $totalValuation = 0;
        $totalSellingValuation = 0;
        $lowStockCount = 0;

        foreach ($parts as $part) {
            $totalValuation += (float) $part->stock_quantity * (float) $part->reference_cost_price;
            $totalSellingValuation += (float) $part->stock_quantity * (float) $part->selling_price;
            if ($part->isLowStock()) {
                $lowStockCount++;
            }
        }

        return [
            'parts' => $parts,
            'total_items' => $parts->count(),
            'low_stock_count' => $lowStockCount,
            'total_cost_valuation' => $totalValuation,
            'total_selling_valuation' => $totalSellingValuation,
            'potential_margin' => $totalSellingValuation - $totalValuation,
        ];
    }
}
