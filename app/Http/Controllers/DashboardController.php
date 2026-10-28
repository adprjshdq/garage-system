<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SparePart;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // Statistical Cards
        $todayServicesCount = WorkOrder::whereDate('created_at', $today)->count();
        $inProgressCount = WorkOrder::where('status', 'in_progress')->count();
        $completedTodayCount = WorkOrder::whereIn('status', ['completed', 'invoiced', 'closed'])
            ->whereDate('completion_time', $today)
            ->count();

        // Invoiced Revenue Today
        $invoicedToday = Invoice::whereDate('invoice_date', $today)
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        // Cash Payments Received Today
        $actualPaymentsToday = Payment::whereDate('payment_date', $today)
            ->where('status', 'valid')
            ->sum('amount');

        // Unpaid Invoices Count & Outstanding Balance
        $unpaidInvoicesCount = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->count();
        $totalOutstandingReceivable = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_due');

        // Low Stock Count
        $lowStockCount = SparePart::whereColumn('stock_quantity', '<=', 'min_stock')->count();

        // Chart Data: Last 7 days revenue (Both Invoiced and Paid)
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->translatedFormat('d M');

            $invoicedAmount = Invoice::whereDate('invoice_date', $dateStr)
                ->where('status', '!=', 'cancelled')
                ->sum('grand_total');

            $paidAmount = Payment::whereDate('payment_date', $dateStr)
                ->where('status', 'valid')
                ->sum('amount');

            $last7Days[] = [
                'date' => $label,
                'invoiced' => (float) $invoicedAmount,
                'paid' => (float) $paidAmount,
            ];
        }

        // WO Status Breakdown
        $woStatusCounts = WorkOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent Work Orders
        $recentWorkOrders = WorkOrder::with(['customer', 'vehicle', 'mechanics'])
            ->latest()
            ->take(6)
            ->get();

        // Active Vehicles in progress
        $activeWorkOrders = WorkOrder::with(['customer', 'vehicle', 'mechanics'])
            ->where('status', 'in_progress')
            ->latest('start_time')
            ->take(5)
            ->get();

        // Recent Invoices
        $recentInvoices = Invoice::with(['customer'])
            ->latest()
            ->take(5)
            ->get();

        // Low stock parts
        $lowStockParts = SparePart::with('category')
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get();

        // Recent audit logs
        $recentAudits = AuditLog::with('user')
            ->latest('created_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'todayServicesCount',
            'inProgressCount',
            'completedTodayCount',
            'invoicedToday',
            'actualPaymentsToday',
            'unpaidInvoicesCount',
            'totalOutstandingReceivable',
            'lowStockCount',
            'last7Days',
            'woStatusCounts',
            'recentWorkOrders',
            'activeWorkOrders',
            'recentInvoices',
            'lowStockParts',
            'recentAudits'
        ));
    }
}
