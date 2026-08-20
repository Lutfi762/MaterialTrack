<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apps\DashboardController;
use App\Http\Controllers\Apps\PermissionController;
use App\Http\Controllers\Apps\RoleController;
use App\Http\Controllers\Apps\UserController;
use App\Http\Controllers\Apps\CategoryController;
use App\Http\Controllers\Apps\ItemController;
use App\Http\Controllers\Apps\StockInController;
use App\Http\Controllers\Apps\StockOutController;
use App\Http\Controllers\Apps\StockMovementController;
use App\Http\Controllers\Apps\StockOpnameController;
use App\Http\Controllers\Apps\DepartmentController;
use App\Http\Controllers\Apps\PicController;
use App\Http\Controllers\Apps\LocationController;
use App\Http\Controllers\Apps\AIAssistantController;
use App\Http\Controllers\Apps\ReportController;
use App\Http\Controllers\Apps\NotificationController;
use App\Http\Controllers\Apps\StockTransferController;
use App\Http\Controllers\Apps\ActivityLogController;
use App\Http\Controllers\Apps\StockReservationController;
use App\Http\Controllers\Apps\ForecastingController;
use App\Http\Controllers\Apps\CustomReportController;
use App\Http\Controllers\Apps\WarehouseController;
use App\Http\Controllers\Apps\SupplierController;
use App\Http\Controllers\Apps\BulkStockController;
use App\Http\Controllers\Apps\StockReturnController;
use App\Http\Controllers\Apps\StockAdjustmentController;
use App\Http\Controllers\Apps\StockAlertConfigController;
use App\Http\Controllers\Apps\CompanyProfileController;
use App\Http\Controllers\Apps\CustomerController;
use App\Http\Controllers\Apps\PurchaseRequestController;
use App\Http\Controllers\Apps\PurchaseOrderController;
use App\Http\Controllers\Apps\GoodsReceiptController;
use App\Http\Controllers\Apps\PurchaseInvoiceController;
use App\Http\Controllers\Apps\QuotationController;
use App\Http\Controllers\Apps\SalesOrderController;
use App\Http\Controllers\Apps\DeliveryController;
use App\Http\Controllers\Apps\SalesInvoiceController;
use App\Http\Controllers\Apps\ChartOfAccountController;
use App\Http\Controllers\Apps\JournalEntryController;
use App\Http\Controllers\Apps\EmployeeController;
use App\Http\Controllers\Apps\AttendanceController;
use App\Http\Controllers\Apps\LeaveController;
use App\Http\Controllers\Apps\PayrollController;
use App\Http\Controllers\Apps\ApprovalWorkflowController;

// Halaman Utama → Login
Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest');

// API route for updating user locale
Route::post('/api/user/locale', function () {
    $validated = request()->validate([
        'locale' => 'required|in:en,id',
    ]);

    auth()->user()->update(['locale' => $validated['locale']]);

    return response()->json(['success' => true]);
})->middleware('auth');

// Group Route "apps"
Route::prefix('apps')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('apps.dashboard');
    Route::get('/dashboard/pdf', [DashboardController::class, 'downloadPdf'])
        ->name('apps.dashboard.pdf')
        ->middleware('throttle:10,1');

    // Permissions
    Route::get('/permissions', PermissionController::class)
        ->name('apps.permissions.index')
        ->middleware('permission:permissions.index');

    // Roles
    Route::resource('/roles', RoleController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:roles.index|roles.create|roles.edit|roles.delete');

    // Users
    Route::resource('/users', UserController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:users.index|users.create|users.edit|users.delete');

    // Categories
    Route::resource('/categories', CategoryController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:categories.index|categories.create|categories.edit|categories.delete');

    // Items
    Route::get('/items/lookup', [ItemController::class, 'lookupByCode'])
        ->name('apps.items.lookup')
        ->middleware('permission:items.index');
    Route::get('/items/{item}/history', [ItemController::class, 'history'])
        ->name('apps.items.history')
        ->middleware('permission:items.index');
    Route::resource('/items', ItemController::class, ['as' => 'apps'])
        ->middleware('permission:items.index|items.create|items.edit|items.delete');
    Route::post('/items/import', [ItemController::class, 'import'])
        ->name('apps.items.import')
        ->middleware('permission:items.create');

    // Stock In
    Route::resource('/stock-ins', StockInController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:stock_ins.index|stock_ins.create|stock_ins.edit|stock_ins.delete');
    Route::get('/stock-ins/{stock_in}/pdf', [StockInController::class, 'downloadPdf'])
        ->name('apps.stock-ins.pdf')
        ->middleware(['permission:stock_ins.index', 'throttle:30,1']);
    Route::get('/stock-ins/export/excel', [StockInController::class, 'exportExcel'])
        ->name('apps.stock-ins.export')
        ->middleware(['permission:stock_ins.index', 'throttle:10,1']);
    Route::get('/stock-ins/download-all/pdf', [StockInController::class, 'downloadAllPdf'])
        ->name('apps.stock-ins.download-all-pdf')
        ->middleware(['permission:stock_ins.index', 'throttle:10,1']);

    // Stock Out
    Route::resource('/stock-outs', StockOutController::class, ['as' => 'apps'])
        ->middleware('permission:stock_outs.index|stock_outs.create|stock_outs.edit|stock_outs.delete');
    Route::get('/stock-outs/{stock_out}/pdf', [StockOutController::class, 'downloadPdf'])
        ->name('apps.stock-outs.pdf')
        ->middleware(['permission:stock_outs.index', 'throttle:30,1']);
    Route::get('/stock-outs/export/excel', [StockOutController::class, 'exportExcel'])
        ->name('apps.stock-outs.export')
        ->middleware(['permission:stock_outs.index', 'throttle:10,1']);
    Route::get('/stock-outs/download-all/pdf', [StockOutController::class, 'downloadAllPdf'])
        ->name('apps.stock-outs.download-all-pdf')
        ->middleware(['permission:stock_outs.index', 'throttle:10,1']);
    Route::patch('/stock-outs/{stock_out}/approve', [StockOutController::class, 'approve'])
        ->name('apps.stock-outs.approve')
        ->middleware('permission:stock_outs.approve');
    Route::patch('/stock-outs/{stock_out}/reject', [StockOutController::class, 'reject'])
        ->name('apps.stock-outs.reject')
        ->middleware('permission:stock_outs.approve');

    // Stock Movements
    Route::get('/stock-movements', [StockMovementController::class, 'index'])
        ->name('apps.stock-movements.index')
        ->middleware('permission:stock_movements.index');
    Route::get('/stock-movements/download-all/pdf', [StockMovementController::class, 'downloadAllPdf'])
        ->name('apps.stock-movements.download-all-pdf')
        ->middleware(['permission:stock_movements.index', 'throttle:10,1']);
    Route::get('/stock-movements/export/excel', [StockMovementController::class, 'exportExcel'])
        ->name('apps.stock-movements.export')
        ->middleware('permission:stock_movements.index')
        ->middleware('throttle:10,1');

    // Stock Opnames
    Route::get('/stock-opnames', [StockOpnameController::class, 'index'])
        ->name('apps.stock-opnames.index')
        ->middleware('permission:stock_opnames.index');
    Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])
        ->name('apps.stock-opnames.create')
        ->middleware('permission:stock_opnames.create');
    Route::post('/stock-opnames', [StockOpnameController::class, 'store'])
        ->name('apps.stock-opnames.store')
        ->middleware('permission:stock_opnames.create');
    Route::get('/stock-opnames/export/excel', [StockOpnameController::class, 'exportExcel'])
        ->name('apps.stock-opnames.export')
        ->middleware('permission:stock_opnames.index')
        ->middleware('throttle:10,1');
    Route::get('/stock-opnames/download-all/pdf', [StockOpnameController::class, 'downloadAllPdf'])
        ->name('apps.stock-opnames.download-all-pdf')
        ->middleware(['permission:stock_opnames.index', 'throttle:10,1']);
    Route::get('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'show'])
        ->name('apps.stock-opnames.show')
        ->middleware('permission:stock_opnames.index');
    Route::get('/stock-opnames/{stockOpname}/edit', [StockOpnameController::class, 'edit'])
        ->name('apps.stock-opnames.edit')
        ->middleware('permission:stock_opnames.create');
    Route::put('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'update'])
        ->name('apps.stock-opnames.update')
        ->middleware('permission:stock_opnames.create');
    Route::delete('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'destroy'])
        ->name('apps.stock-opnames.destroy')
        ->middleware('permission:stock_opnames.create');
    Route::patch('/stock-opnames/{stockOpname}/verify', [StockOpnameController::class, 'verify'])
        ->name('apps.stock-opnames.verify')
        ->middleware('permission:stock_opnames.verify');
    Route::get('/stock-opnames/{stockOpname}/pdf', [StockOpnameController::class, 'downloadPdf'])
        ->name('apps.stock-opnames.pdf')
        ->middleware(['permission:stock_opnames.index', 'throttle:30,1']);

    // Stock Adjustments
    Route::resource('/stock-adjustments', StockAdjustmentController::class, ['as' => 'apps'])
        ->except(['show', 'edit', 'update'])
        ->middleware('permission:stock_adjustments.index|stock_adjustments.create|stock_adjustments.approve');
    Route::patch('/stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])
        ->name('apps.stock-adjustments.approve')
        ->middleware('permission:stock_adjustments.approve');
    Route::patch('/stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])
        ->name('apps.stock-adjustments.reject')
        ->middleware('permission:stock_adjustments.approve');

    // Departments
    Route::resource('/departments', DepartmentController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:departments.index|departments.create|departments.edit|departments.delete');

    // PICs
    Route::resource('/pics', PicController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:pics.index|pics.create|pics.edit|pics.delete');

    // Locations
    Route::get('/locations/map', [LocationController::class, 'map'])
        ->name('apps.locations.map')
        ->middleware('permission:locations.index');
    Route::resource('/locations', LocationController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:locations.index|locations.create|locations.edit|locations.delete');

    // AI Assistant
    Route::get('/ai-assistant', [AIAssistantController::class, 'index'])
        ->name('apps.ai-assistant.index')
        ->middleware('permission:ai_assistant.use');
    Route::post('/ai-assistant/chat', [AIAssistantController::class, 'chat'])
        ->name('apps.ai-assistant.chat')
        ->middleware(['permission:ai_assistant.use', 'throttle:' . config('ai.assistant.rate_limit', '20,1')]);
    Route::get('/ai-assistant/conversations', [AIAssistantController::class, 'conversations'])
        ->name('apps.ai-assistant.conversations')
        ->middleware(['permission:ai_assistant.use', 'throttle:30,1']);
    Route::get('/ai-assistant/conversations/{id}', [AIAssistantController::class, 'loadConversation'])
        ->name('apps.ai-assistant.load-conversation')
        ->middleware(['permission:ai_assistant.use', 'throttle:60,1']);
    Route::delete('/ai-assistant/conversations/{id}', [AIAssistantController::class, 'deleteConversation'])
        ->name('apps.ai-assistant.delete-conversation')
        ->middleware(['permission:ai_assistant.use', 'throttle:20,1']);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('apps.reports.index')
        ->middleware('permission:reports.index');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('apps.notifications.index')
        ->middleware('auth');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('apps.notifications.mark-read')
        ->middleware('auth');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('apps.notifications.mark-all-read')
        ->middleware('auth');
    Route::post('/notifications/refresh', [NotificationController::class, 'refresh'])
        ->name('apps.notifications.refresh')
        ->middleware('auth');

    // Stock Transfers
    Route::resource('/stock-transfers', StockTransferController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:stock_ins.index|stock_ins.create');
    Route::post('/stock-transfers/{stockTransfer}/approve', [StockTransferController::class, 'approve'])
        ->name('apps.stock-transfers.approve')
        ->middleware('permission:stock_outs.approve');
    Route::post('/stock-transfers/{stockTransfer}/reject', [StockTransferController::class, 'reject'])
        ->name('apps.stock-transfers.reject')
        ->middleware('permission:stock_outs.approve');
    Route::get('/stock-transfers/{stockTransfer}/pdf', [StockTransferController::class, 'downloadPdf'])
        ->name('apps.stock-transfers.pdf')
        ->middleware('permission:stock_ins.index');
    Route::get('/stock-transfers/download-all/pdf', [StockTransferController::class, 'downloadAllPdf'])
        ->name('apps.stock-transfers.download-all-pdf')
        ->middleware(['permission:stock_ins.index', 'throttle:10,1']);

    // Profile
    Route::get('/profile', function () {
        return Inertia::render('Apps/Profile/Index');
    })->name('apps.profile');

    // Activity Log
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->name('apps.activity-logs')
        ->middleware('permission:activity_logs.index');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'exportCsv'])
        ->name('apps.activity-logs.export')
        ->middleware(['permission:activity_logs.index', 'throttle:10,1']);

    // Bulk Stock Operations
    Route::resource('/bulk-stock', BulkStockController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:stock_ins.index|stock_ins.create');

    // Suppliers
    Route::resource('/suppliers', SupplierController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:items.index|items.create|items.edit|items.delete');

    // Stock Reservations
    Route::resource('/stock-reservations', StockReservationController::class, ['as' => 'apps'])
        ->except(['edit', 'update'])
        ->middleware('permission:stock_ins.index');
    Route::post('/stock-reservations/{stockReservation}/fulfill', [StockReservationController::class, 'fulfill'])
        ->name('apps.stock-reservations.fulfill')
        ->middleware('permission:stock_ins.index');
    Route::post('/stock-reservations/{stockReservation}/cancel', [StockReservationController::class, 'cancel'])
        ->name('apps.stock-reservations.cancel')
        ->middleware('permission:stock_ins.index');

    // Stock Returns
    Route::get('/stock-returns', [StockReturnController::class, 'index'])
        ->name('apps.stock-returns.index')
        ->middleware('permission:stock_ins.index');
    Route::get('/stock-returns/create', [StockReturnController::class, 'create'])
        ->name('apps.stock-returns.create')
        ->middleware('permission:stock_ins.create');
    Route::post('/stock-returns', [StockReturnController::class, 'store'])
        ->name('apps.stock-returns.store')
        ->middleware('permission:stock_ins.create');
    Route::post('/stock-returns/{stockReturn}/approve', [StockReturnController::class, 'approve'])
        ->name('apps.stock-returns.approve')
        ->middleware('permission:stock_outs.approve');
    Route::post('/stock-returns/{stockReturn}/reject', [StockReturnController::class, 'reject'])
        ->name('apps.stock-returns.reject')
        ->middleware('permission:stock_outs.approve');

    // Stock Alert Config
    Route::get('/stock-alerts', [StockAlertConfigController::class, 'index'])
        ->name('apps.stock-alerts.index')
        ->middleware('permission:stock_alerts.index');
    Route::put('/stock-alerts', [StockAlertConfigController::class, 'update'])
        ->name('apps.stock-alerts.update')
        ->middleware('permission:stock_alerts.edit');

    // Stock Forecasting
    Route::get('/forecasting', [ForecastingController::class, 'index'])
        ->name('apps.forecasting.index')
        ->middleware('permission:items.index');

    // Custom Reports
    Route::get('/custom-reports', [CustomReportController::class, 'index'])
        ->name('apps.custom-reports.index')
        ->middleware('permission:reports.index');
    Route::post('/custom-reports/generate', [CustomReportController::class, 'generate'])
        ->name('apps.custom-reports.generate')
        ->middleware('permission:reports.index');

    // Warehouses
    Route::resource('/warehouses', WarehouseController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:items.index|items.create|items.edit|items.delete');

    // --- ERP MODULE ROUTES ---

    // Company Settings
    Route::get('/company-profile', [CompanyProfileController::class, 'index'])->name('apps.company-profile.index')
        ->middleware('permission:company_profile.index');
    Route::post('/company-profile', [CompanyProfileController::class, 'update'])->name('apps.company-profile.update')
        ->middleware('permission:company_profile.edit');

    // Customers
    Route::resource('/customers', CustomerController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:customers.index|customers.create|customers.edit|customers.delete');

    // Procurement
    Route::resource('/purchase-requests', PurchaseRequestController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:purchase_requests.index|purchase_requests.create');
    Route::post('/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('apps.purchase-requests.approve')
        ->middleware('permission:purchase_requests.approve');
    Route::post('/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('apps.purchase-requests.reject')
        ->middleware('permission:purchase_requests.approve');

    Route::resource('/purchase-orders', PurchaseOrderController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:purchase_orders.index|purchase_orders.create');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('apps.purchase-orders.approve')
        ->middleware('permission:purchase_orders.approve');

    Route::resource('/goods-receipts', GoodsReceiptController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:goods_receipts.index|goods_receipts.create');
    Route::resource('/purchase-invoices', PurchaseInvoiceController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:purchase_invoices.index|purchase_invoices.create');

    // Sales & Distribution
    Route::resource('/quotations', QuotationController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:quotations.index|quotations.create');

    Route::resource('/sales-orders', SalesOrderController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:sales_orders.index|sales_orders.create');
    Route::post('/sales-orders/{salesOrder}/approve', [SalesOrderController::class, 'approve'])->name('apps.sales-orders.approve')
        ->middleware('permission:sales_orders.approve');

    Route::resource('/deliveries', DeliveryController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:deliveries.index|deliveries.create');
    Route::resource('/sales-invoices', SalesInvoiceController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:sales_invoices.index|sales_invoices.create');

    // Finance & Accounting
    Route::resource('/chart-of-accounts', ChartOfAccountController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:chart_of_accounts.index|chart_of_accounts.create|chart_of_accounts.edit|chart_of_accounts.delete');
    Route::resource('/journal-entries', JournalEntryController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:journal_entries.index|journal_entries.create');

    // HRM & Payroll
    Route::resource('/employees', EmployeeController::class, ['as' => 'apps'])
        ->except(['show'])
        ->middleware('permission:employees.index|employees.create|employees.edit|employees.delete');
    Route::resource('/attendances', AttendanceController::class, ['as' => 'apps'])
        ->except(['show', 'edit', 'update', 'destroy'])
        ->middleware('permission:attendances.index|attendances.create');

    Route::resource('/leaves', LeaveController::class, ['as' => 'apps'])
        ->except(['show', 'edit', 'update', 'destroy'])
        ->middleware('permission:leaves.index|leaves.create');
    Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('apps.leaves.approve')
        ->middleware('permission:leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('apps.leaves.reject')
        ->middleware('permission:leaves.approve');

    Route::resource('/payroll', PayrollController::class, ['as' => 'apps'])
        ->except(['edit', 'update', 'destroy'])
        ->middleware('permission:payroll.index|payroll.create');
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('apps.payroll.approve')
        ->middleware('permission:payroll.approve');
    Route::post('/payroll/{payroll}/process', [PayrollController::class, 'process'])->name('apps.payroll.process')
        ->middleware('permission:payroll.approve');

    // Approval Workflows
    Route::resource('/approval-workflows', ApprovalWorkflowController::class, ['as' => 'apps'])
        ->except(['show', 'edit', 'update', 'destroy'])
        ->middleware('permission:approval_workflows.index|approval_workflows.create');

});