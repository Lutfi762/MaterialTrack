<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Pic;
use App\Models\StockAdjustment;
use App\Models\StockAlertConfig;
use App\Models\StockIn;
use App\Models\StockOpname;
use App\Models\StockOut;
use App\Models\StockReservation;
use App\Models\StockReturn;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Observers\GlobalActivityObserver;
use App\Observers\StockAdjustmentObserver;
use App\Observers\StockInObserver;
use App\Observers\StockOpnameObserver;
use App\Observers\StockOutObserver;
use App\Observers\StockReturnObserver;
use App\Observers\StockTransferObserver;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Kebijakan password: minimal 8 karakter, wajib huruf & angka
        // (berlaku untuk register / reset / update password via Fortify).
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Register observers untuk stock movement audit trail
        StockIn::observe(StockInObserver::class);
        StockOut::observe(StockOutObserver::class);
        StockOpname::observe(StockOpnameObserver::class);
        StockTransfer::observe(StockTransferObserver::class);
        StockReturn::observe(StockReturnObserver::class);
        StockAdjustment::observe(StockAdjustmentObserver::class);

        // Activity log terpusat: semua perubahan model tercatat otomatis
        foreach ([
            Item::class, Category::class, Department::class, Location::class,
            Pic::class, User::class, Supplier::class, Warehouse::class,
            StockIn::class, StockOut::class, StockOpname::class,
            StockTransfer::class, StockReturn::class, StockReservation::class,
            StockAdjustment::class, StockAlertConfig::class,
            Role::class, Permission::class,
        ] as $model) {
            $model::observe(GlobalActivityObserver::class);
        }

        // Trailing login/logout untuk audit keamanan
        Event::listen(Login::class, fn (Login $event) => ActivityLogService::log('auth.login', $event->user, [], null, $event->user?->id));
        Event::listen(Logout::class, fn (Logout $event) => ActivityLogService::log('auth.logout', $event->user, [], null, $event->user?->id));
        Event::listen(Failed::class, fn (Failed $event) => ActivityLogService::log(
            'auth.login_failed',
            null,
            ['email' => $event->credentials['email'] ?? null]
        ));

        // Generate notifications setiap jam 8 pagi
        Schedule::call(function () {
            NotificationService::generateAll();
        })->dailyAt('08:00');

        // Kirim email alert stok menipis setiap hari Senin jam 9 pagi
        Schedule::command('report:low-stock')->weekly()->mondays()->at('09:00');

        RateLimiter::for('login', function ($request) {
            $throttleKey = strtolower($request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        // Fortify juga butuh limiter 'two-factor' kalau 2FA aktif
        RateLimiter::for('two-factor', function ($request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
