<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apps\DashboardController;

// 1. Route Halaman Utama (Langsung Render Login View, JANGAN Pakai redirect)
Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->middleware('guest');

// 2. Group Route "apps" dengan Middleware "auth"
Route::prefix('apps')->group(function () {
    Route::group(['middleware' => ['auth']], function () {

        // Route Dashboard MaterialTrack
        Route::get('dashboard', DashboardController::class)->name('apps.dashboard');

    });
});