<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Item;
use App\Models\StockOut;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public static function generateAll(): void
    {
        self::checkLowStock();
        self::checkPendingApproval();
        self::checkExpiringItems();
    }

    public static function checkLowStock(): void
    {
        $lowStockItems = Item::lowStock()->get();
        $outOfStockItems = Item::outOfStock()->get();

        $adminIds = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->pluck('id');

        foreach ($adminIds as $userId) {
            foreach ($lowStockItems as $item) {
                $exists = AppNotification::where('user_id', $userId)
                    ->where('type', 'low_stock')
                    ->where('message', 'like', '%' . $item->code . '%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    AppNotification::create([
                        'user_id' => $userId,
                        'type' => 'low_stock',
                        'title' => 'Stok Menipis',
                        'message' => "{$item->code} - {$item->name}: stok tersisa {$item->current_stock} {$item->unit} (min: {$item->min_stock})",
                        'link' => '/apps/items',
                    ]);
                }
            }

            foreach ($outOfStockItems as $item) {
                $exists = AppNotification::where('user_id', $userId)
                    ->where('type', 'out_of_stock')
                    ->where('message', 'like', '%' . $item->code . '%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    AppNotification::create([
                        'user_id' => $userId,
                        'type' => 'out_of_stock',
                        'title' => 'Stok Habis',
                        'message' => "{$item->code} - {$item->name}: stok sudah habis!",
                        'link' => '/apps/items',
                    ]);
                }
            }
        }
    }

    public static function checkPendingApproval(): void
    {
        $pendingCount = StockOut::where('status', 'pending')->count();

        if ($pendingCount > 0) {
            $adminIds = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->pluck('id');

            foreach ($adminIds as $userId) {
                $exists = AppNotification::where('user_id', $userId)
                    ->where('type', 'pending_approval')
                    ->where('created_at', '>=', now()->subDays(1))
                    ->exists();

                if (!$exists) {
                    AppNotification::create([
                        'user_id' => $userId,
                        'type' => 'pending_approval',
                        'title' => 'Approval Tertunda',
                        'message' => "Ada {$pendingCount} material keluar yang menunggu persetujuan anda.",
                        'link' => '/apps/stock-outs',
                    ]);
                }
            }
        }
    }

    public static function checkExpiringItems(): void
    {
        $expiringItems = StockIn::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->with('item')
            ->get()
            ->unique('item_id');

        $adminIds = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->pluck('id');

        foreach ($adminIds as $userId) {
            foreach ($expiringItems as $stockIn) {
                if (!$stockIn->item) continue;

                $daysLeft = now()->diffInDays($stockIn->expiry_date);
                $exists = AppNotification::where('user_id', $userId)
                    ->where('type', 'expiring_item')
                    ->where('message', 'like', '%' . $stockIn->item->code . '%')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if (!$exists) {
                    AppNotification::create([
                        'user_id' => $userId,
                        'type' => 'expiring_item',
                        'title' => 'Item Akan Kedaluwarsa',
                        'message' => "{$stockIn->item->code} - {$stockIn->item->name} akan kedaluwarsa dalam {$daysLeft} hari ({$stockIn->expiry_date})",
                        'link' => '/apps/stock-ins',
                    ]);
                }
            }
        }
    }

    public static function markAsRead(int $notificationId, int $userId): bool
    {
        return AppNotification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->update(['is_read' => true]) > 0;
    }

    public static function markAllRead(int $userId): int
    {
        return AppNotification::where('user_id', $userId)
            ->unread()
            ->update(['is_read' => true]);
    }
}
