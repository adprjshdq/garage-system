<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function services(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $status = $request->input('status');
        $mechanicId = $request->input('mechanic_id');

        $report = $this->reportingService->getServiceReport($startDate, $endDate, $status, $mechanicId);
        $mechanics = Mechanic::where('is_active', true)->orderBy('name')->get();

        return view('reports.services', [
            'orders' => $report['data'],
            'summary' => $report['summary'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'mechanicId' => $mechanicId,
            'mechanics' => $mechanics,
        ]);
    }

    public function revenue(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $report = $this->reportingService->getRevenueReport($startDate, $endDate);

        return view('reports.revenue', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    public function inventory(): View
    {
        $report = $this->reportingService->getInventoryReport();

        return view('reports.inventory', $report);
    }

    public function mechanics(Request $request): View
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $mechanics = Mechanic::withCount([
            'workOrders as total_assigned' => function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->whereDate('work_orders.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('work_orders.created_at', '<=', $endDate);
                }
            },
            'workOrders as total_completed' => function ($q) use ($startDate, $endDate) {
                $q->whereIn('work_orders.status', ['completed', 'invoiced', 'closed']);
                if ($startDate) {
                    $q->whereDate('work_orders.completion_time', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('work_orders.completion_time', '<=', $endDate);
                }
            },
        ])->get();

        return view('reports.mechanics', compact('mechanics', 'startDate', 'endDate'));
    }
}
