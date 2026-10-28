<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MechanicController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceCatalogController;
use App\Http\Controllers\ServiceReceptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SparePartCategoryController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Operasional Bengkel: Pelanggan & Kendaraan
    Route::resource('customers', CustomerController::class);
    Route::resource('vehicles', VehicleController::class);

    // Penerimaan Kendaraan
    Route::get('/receptions', [ServiceReceptionController::class, 'index'])->name('receptions.index');
    Route::get('/receptions/create', [ServiceReceptionController::class, 'create'])->name('receptions.create');
    Route::post('/receptions', [ServiceReceptionController::class, 'store'])->name('receptions.store');
    Route::get('/receptions/{reception}', [ServiceReceptionController::class, 'show'])->name('receptions.show');
    Route::get('/receptions/{reception}/print', [ServiceReceptionController::class, 'print'])->name('receptions.print');

    // Work Orders
    Route::resource('work-orders', WorkOrderController::class);
    Route::post('/work-orders/{work_order}/approve', [WorkOrderController::class, 'approve'])->name('work-orders.approve');
    Route::post('/work-orders/{work_order}/start', [WorkOrderController::class, 'startWork'])->name('work-orders.start');
    Route::post('/work-orders/{work_order}/items/{item}/issue', [WorkOrderController::class, 'issuePart'])->name('work-orders.issue-part');
    Route::post('/work-orders/{work_order}/inspect', [WorkOrderController::class, 'inspect'])->name('work-orders.inspect');
    Route::post('/work-orders/{work_order}/cancel', [WorkOrderController::class, 'cancel'])->name('work-orders.cancel');
    Route::get('/work-orders/{work_order}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');

    // Master Data
    Route::resource('mechanics', MechanicController::class);
    Route::resource('services', ServiceCatalogController::class);

    // Spare Part Categories & Movements
    Route::get('/spare-parts/categories', [SparePartCategoryController::class, 'index'])->name('spare-parts.categories');
    Route::post('/spare-parts/categories', [SparePartCategoryController::class, 'store'])->name('spare-parts.categories.store');
    Route::put('/spare-parts/categories/{category}', [SparePartCategoryController::class, 'update'])->name('spare-parts.categories.update');
    Route::delete('/spare-parts/categories/{category}', [SparePartCategoryController::class, 'destroy'])->name('spare-parts.categories.destroy');
    Route::get('/spare-parts/movements', [SparePartController::class, 'movements'])->name('spare-parts.movements');
    Route::post('/spare-parts/{spare_part}/adjust', [SparePartController::class, 'adjustStock'])->name('spare-parts.adjust');
    Route::resource('spare-parts', SparePartController::class);

    // Suppliers & Purchases
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchases', PurchaseOrderController::class);
    Route::post('/purchases/{purchase}/receive', [PurchaseOrderController::class, 'receiveGoods'])->name('purchases.receive');
    Route::post('/purchases/{purchase}/return', [PurchaseOrderController::class, 'returnGoods'])->name('purchases.return');
    Route::get('/purchases/{purchase}/print', [PurchaseOrderController::class, 'print'])->name('purchases.print');

    // Invoices & Payments
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/invoices/{invoice}/print-thermal', [InvoiceController::class, 'printThermal'])->name('invoices.print-thermal');
    Route::get('/invoices/{invoice}/print-formal', [InvoiceController::class, 'printFormal'])->name('invoices.print-formal');
    Route::get('/invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{payment}/void', [PaymentController::class, 'voidPayment'])->name('payments.void');
    Route::get('/receivables', [PaymentController::class, 'receivables'])->name('payments.receivables');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/categories', [ExpenseController::class, 'categories'])->name('expenses.categories');
    Route::post('/expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/reports/mechanics', [ReportController::class, 'mechanics'])->name('reports.mechanics');

    // Owner Only System Management
    Route::middleware('role:owner')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
