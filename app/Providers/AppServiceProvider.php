<?php

namespace App\Providers;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Observers\StockInObserver;
use App\Observers\StockOutObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register observers untuk stock movement audit trail
        StockIn::observe(StockInObserver::class);
        StockOut::observe(StockOutObserver::class);

        RateLimiter::for('login', function ($request) {
            $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        // Fortify juga butuh limiter 'two-factor' kalau 2FA aktif
        RateLimiter::for('two-factor', function ($request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}