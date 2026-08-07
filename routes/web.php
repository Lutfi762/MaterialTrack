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

// Halaman Utama → Login
Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest');

// Group Route "apps"
Route::prefix('apps')->middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('apps.dashboard');

    // Permissions
    Route::get('/permissions', PermissionController::class)
        ->name('apps.permissions.index')
        ->middleware('permission:permissions.index');

    // Roles
    Route::resource('/roles', RoleController::class, ['as' => 'apps'])
        ->middleware('permission:roles.index|roles.create|roles.edit|roles.delete');

    // Users
    Route::resource('/users', UserController::class, ['as' => 'apps']);

    // Categories
    Route::resource('/categories', CategoryController::class, ['as' => 'apps']);

    // Items
    Route::resource('/items', ItemController::class, ['as' => 'apps']);

    // Stock In
    Route::resource('/stock-ins', StockInController::class, ['as' => 'apps']);
    Route::get('/stock-ins/{stock_in}/pdf', [StockInController::class, 'downloadPdf'])->name('apps.stock-ins.pdf');
    Route::get('/stock-ins/export/excel', [StockInController::class, 'exportExcel'])->name('apps.stock-ins.export');
    Route::get('/stock-ins/download-all/pdf', [StockInController::class, 'downloadAllPdf'])->name('apps.stock-ins.download-all-pdf');
    // Stock Out
    Route::resource('/stock-outs', StockOutController::class, ['as' => 'apps']);
    Route::get('/stock-outs/{stock_out}/pdf', [StockOutController::class, 'downloadPdf'])->name('apps.stock-outs.pdf');
    Route::get('/stock-outs/export/excel', [StockOutController::class, 'exportExcel'])->name('apps.stock-outs.export');
    Route::get('/stock-outs/download-all/pdf', [StockOutController::class, 'downloadAllPdf'])->name('apps.stock-outs.download-all-pdf');
    // Stock Movements
    Route::get('/stock-movements', [StockMovementController::class, 'index'])
    ->name('apps.stock-movements.index');
  Route::get('/stock-movements/download-all/pdf', [StockMovementController::class, 'downloadAllPdf'])
    ->name('apps.stock-movements.download-all-pdf');
    Route::get('/stock-movements/export/excel', [StockMovementController::class, 'exportExcel'])
    ->name('apps.stock-movements.export');
   // Stock Opnames
Route::get('/stock-opnames', [StockOpnameController::class, 'index'])->name('apps.stock-opnames.index');
Route::get('/stock-opnames/create', [StockOpnameController::class, 'create'])->name('apps.stock-opnames.create');
Route::post('/stock-opnames', [StockOpnameController::class, 'store'])->name('apps.stock-opnames.store');
Route::get('/stock-opnames/export/excel', [StockOpnameController::class, 'exportExcel'])->name('apps.stock-opnames.export');
Route::get('/stock-opnames/download-all/pdf', [StockOpnameController::class, 'downloadAllPdf'])->name('apps.stock-opnames.download-all-pdf');
Route::get('/stock-opnames/{stockOpname}', [StockOpnameController::class, 'show'])->name('apps.stock-opnames.show');
Route::get('/stock-opnames/{stockOpname}/pdf', [StockOpnameController::class, 'downloadPdf'])->name('apps.stock-opnames.pdf');
    // Departments
    Route::resource('/departments', DepartmentController::class, ['as' => 'apps']);

    // PICs
    Route::resource('/pics', PicController::class, ['as' => 'apps']);

    // Locations
    Route::resource('/locations', LocationController::class, ['as' => 'apps']);

});