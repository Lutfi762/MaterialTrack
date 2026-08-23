<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Caching Service untuk MaterialTrack System
 * Optimasi untuk ratusan concurrent users
 */
class CacheService
{
    // Cache TTL constants (dalam detik)
    const DASHBOARD_TTL = 300;      // 5 menit

    const ITEM_LIST_TTL = 180;      // 3 menit

    const DEPARTMENT_TTL = 3600;    // 1 jam

    const CATEGORY_TTL = 3600;      // 1 jam

    const STOCK_STATS_TTL = 120;    // 2 menit

    const USER_PERMISSIONS_TTL = 1800; // 30 menit

    // Cache key patterns
    const KEY_DASHBOARD = 'dashboard_stats_{department}_{date_from}_{date_to}';

    const KEY_ITEMS_LIST = 'items_list_{department}_{category}_{status}_{page}_{search}';

    const KEY_DEPARTMENTS = 'departments_list_{user_role}_{user_department}';

    const KEY_CATEGORIES = 'categories_list_{department}';

    const KEY_LOW_STOCK = 'low_stock_items_{department}';

    const KEY_USER_PERMS = 'user_permissions_{user_id}';

    /**
     * Get dashboard statistics dengan caching
     */
    public static function getDashboardStats(?int $departmentId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $cacheKey = str_replace(
            ['{department}', '{date_from}', '{date_to}'],
            [$departmentId ?? 'all', $dateFrom ?? 'default', $dateTo ?? 'default'],
            self::KEY_DASHBOARD
        );
        $cacheKey .= '_v'.self::cacheVersion('dashboard', $departmentId ?? 'all');

        // Fallback ke computation jika cache driver tidak tersedia
        try {
            return Cache::remember($cacheKey, self::DASHBOARD_TTL, function () use ($departmentId, $dateFrom, $dateTo) {
                return self::computeDashboardStats($departmentId, $dateFrom, $dateTo);
            });
        } catch (\Exception $e) {
            // Cache tidak tersedia, compute langsung
            return self::computeDashboardStats($departmentId, $dateFrom, $dateTo);
        }
    }

    /**
     * Compute dashboard statistics (heavy operation)
     */
    private static function computeDashboardStats(?int $departmentId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ? Carbon::parse($dateFrom) : now()->startOfMonth();
        $dateTo = $dateTo ? Carbon::parse($dateTo) : now();

        // 1. Chart Pengeluaran Material 7 Hari Terakhir (optimized)
        $stockOutStats = DB::table('stock_outs')
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->toArray();

        // 2. Material Terbanyak Dipakai (dengan join optimization)
        $topMaterials = DB::table('stock_outs')
            ->join('items', 'stock_outs.item_id', '=', 'items.id')
            ->select(
                'items.id',
                'items.name',
                'items.code',
                DB::raw('SUM(stock_outs.qty) as total_out'),
                DB::raw('COUNT(DISTINCT stock_outs.id) as transaction_count')
            )
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->where('stock_outs.department_id', $departmentId);
            })
            ->whereBetween('stock_outs.date', [$dateFrom, $dateTo])
            ->groupBy('items.id', 'items.name', 'items.code')
            ->orderByDesc('total_out')
            ->limit(10)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->toArray();

        // 3. Total Stok Masuk dan Keluar (single query)
        // Build bindings untuk kedua bagian query
        $bindings = [$dateFrom, $dateTo];
        if ($departmentId) {
            $bindings[] = $departmentId;
        }

        // Build bindings untuk bagian kedua
        $bindings2 = [$dateFrom, $dateTo];
        if ($departmentId) {
            $bindings2[] = $departmentId;
        }

        // Gabungkan semua bindings
        $allBindings = array_merge($bindings, $bindings2);

        $stockSummary = DB::select('
            SELECT 
                "in" as type,
                SUM(qty) as total_qty,
                COUNT(*) as transaction_count
            FROM stock_ins
            WHERE date BETWEEN ? AND ?
            '.($departmentId ? ' AND department_id = ?' : '').'
            
            UNION ALL
            
            SELECT 
                "out" as type,
                SUM(qty) as total_qty,
                COUNT(*) as transaction_count
            FROM stock_outs
            WHERE date BETWEEN ? AND ?
            '.($departmentId ? ' AND department_id = ?' : '').'
        ', $allBindings);

        // 4. Items dengan stock menipis (optimized dengan index)
        $lowStockItems = DB::table('items')
            ->select('id', 'name', 'code', 'current_stock', 'min_stock')
            ->whereRaw('current_stock > 0 AND current_stock <= min_stock')
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->whereExists(function ($subquery) use ($departmentId) {
                    $subquery->select(DB::raw(1))
                        ->from('categories')
                        ->join('category_department', 'categories.id', '=', 'category_department.category_id')
                        ->whereRaw('categories.id = items.category_id')
                        ->where('category_department.department_id', $departmentId);
                });
            })
            ->orderByRaw('(current_stock / NULLIF(min_stock, 0)) ASC')
            ->limit(20)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->toArray();

        // 5. Recent stock movements (pagination ready)
        $recentMovements = DB::table('stock_movements')
            ->join('items', 'stock_movements.item_id', '=', 'items.id')
            ->leftJoin('users', 'stock_movements.user_id', '=', 'users.id')
            ->select(
                'stock_movements.id',
                'items.name as item_name',
                'items.code as item_code',
                'stock_movements.type',
                'stock_movements.qty',
                'stock_movements.stock_before',
                'stock_movements.balance_after',
                'stock_movements.created_at',
                'users.name as user_name'
            )
            ->when($departmentId, function ($query) use ($departmentId) {
                return $query->whereExists(function ($subquery) use ($departmentId) {
                    $subquery->select(DB::raw(1))
                        ->from('categories')
                        ->join('category_department', 'categories.id', '=', 'category_department.category_id')
                        ->whereRaw('categories.id = items.category_id')
                        ->where('category_department.department_id', $departmentId);
                });
            })
            ->orderByDesc('stock_movements.created_at')
            ->limit(50)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->values()
            ->toArray();

        return [
            'stock_out_stats' => $stockOutStats,
            'top_materials' => $topMaterials,
            'stock_summary' => array_map(function ($item) {
                return (array) $item; // Convert stdClass to array for better serialization
            }, $stockSummary),
            'low_stock_items' => $lowStockItems,
            'recent_movements' => $recentMovements,
            'computed_at' => now()->toDateTimeString(),
            'cache_ttl' => self::DASHBOARD_TTL,
        ];
    }

    /**
     * Get items list dengan caching dan pagination support
     */
    public static function getItemsList(
        ?int $departmentId = null,
        ?int $categoryId = null,
        ?string $status = null,
        int $page = 1,
        ?string $search = null,
        int $perPage = 10
    ): array {
        $cacheKey = str_replace(
            ['{department}', '{category}', '{status}', '{page}', '{search}'],
            [$departmentId ?? 'all', $categoryId ?? 'all', $status ?? 'all', $page, $search ?? 'none'],
            self::KEY_ITEMS_LIST
        );
        $cacheKey .= '_v'.self::cacheVersion('items', $departmentId ?? 'all');

        return Cache::remember($cacheKey, self::ITEM_LIST_TTL, function () use (
            $departmentId, $categoryId, $status, $page, $search, $perPage
        ) {
            return self::computeItemsList($departmentId, $categoryId, $status, $page, $search, $perPage);
        });
    }

    /**
     * Compute items list dengan optimized queries
     */
    private static function computeItemsList(
        ?int $departmentId,
        ?int $categoryId,
        ?string $status,
        int $page,
        ?string $search,
        int $perPage
    ): array {
        $query = DB::table('items')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->leftJoin('locations', 'items.location_id', '=', 'locations.id')
            ->select(
                'items.id',
                'items.code',
                'items.name',
                'items.description',
                'items.unit',
                'items.min_stock',
                'items.current_stock',
                'items.image',
                'items.created_at',
                'categories.name as category_name',
                'locations.name as location_name'
            );

        // Apply department filter via category_department join
        if ($departmentId) {
            $query->join('category_department', 'categories.id', '=', 'category_department.category_id')
                ->where('category_department.department_id', $departmentId);
        }

        // Apply category filter
        if ($categoryId) {
            $query->where('items.category_id', $categoryId);
        }

        // Apply stock status filter
        if ($status) {
            if ($status === 'aman') {
                $query->whereRaw('items.current_stock > items.min_stock');
            } elseif ($status === 'menipis') {
                $query->whereRaw('items.current_stock > 0 AND items.current_stock <= items.min_stock');
            } elseif ($status === 'habis') {
                $query->where('items.current_stock', '<=', 0);
            }
        }

        // Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('items.name', 'like', "%{$search}%")
                    ->orWhere('items.code', 'like', "%{$search}%");
            });
        }

        // Get total count for pagination
        $total = (clone $query)->count();

        // Apply pagination
        $items = $query->orderBy('items.created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'items' => $items->map(fn ($row) => (array) $row)->values()->toArray(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'computed_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get cache version key untuk invalidation yang driver-agnostic
     */
    private static function cacheVersion(string $scope, string|int $id): int
    {
        return (int) Cache::get("cache_version_{$scope}_{$id}", 0);
    }

    /**
     * Clear cache untuk department tertentu (driver-agnostic)
     */
    public static function clearDepartmentCache(?int $departmentId = null): void
    {
        $scope = $departmentId ?? 'all';

        Cache::increment("cache_version_dashboard_{$scope}");
        Cache::increment("cache_version_items_{$scope}");
        Cache::increment("cache_version_lowstock_{$scope}");
    }

    /**
     * Clear semua cache related ke items — semua scope (all + tiap department),
     * karena list item di-version per department.
     */
    public static function clearItemsCache(): void
    {
        $scopes = ['all'];

        foreach (DB::table('departments')->pluck('id') as $departmentId) {
            $scopes[] = $departmentId;
        }

        foreach ($scopes as $scope) {
            Cache::increment("cache_version_items_{$scope}");
            Cache::increment("cache_version_lowstock_{$scope}");
        }
    }

    /**
     * Get user permissions dengan caching
     */
    public static function getUserPermissions(int $userId): array
    {
        $cacheKey = str_replace('{user_id}', $userId, self::KEY_USER_PERMS);

        return Cache::remember($cacheKey, self::USER_PERMISSIONS_TTL, function () use ($userId) {
            $user = User::with(['roles.permissions', 'permissions'])->find($userId);

            if (! $user) {
                return [];
            }

            $permissions = $user->getAllPermissions()->pluck('name')->toArray();

            return [
                'permissions' => $permissions,
                'roles' => $user->roles->pluck('name')->toArray(),
                'department_id' => $user->department_id,
                'computed_at' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Invalidate user permissions cache
     */
    public static function invalidateUserPermissions(int $userId): void
    {
        $cacheKey = str_replace('{user_id}', $userId, self::KEY_USER_PERMS);
        Cache::forget($cacheKey);
    }

    /**
     * Get cache statistics untuk monitoring (driver-safe)
     */
    public static function getCacheStats(): array
    {
        try {
            $redis = Cache::getStore()->connection();

            return [
                'memory_used' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
                'keys_count' => count($redis->keys('*')),
                'hit_rate' => self::calculateHitRate(),
                'dashboard_cache_size' => count($redis->keys('dashboard_stats_*')),
                'items_cache_size' => count($redis->keys('items_list_*')),
                'last_cleared' => Cache::get('cache_last_cleared') ?? 'Never',
            ];
        } catch (\Exception $e) {
            return [
                'memory_used' => 'N/A',
                'keys_count' => 0,
                'hit_rate' => self::calculateHitRate(),
                'dashboard_cache_size' => 0,
                'items_cache_size' => 0,
                'last_cleared' => Cache::get('cache_last_cleared') ?? 'Never',
            ];
        }
    }

    /**
     * Calculate cache hit rate (simple implementation)
     */
    private static function calculateHitRate(): float
    {
        $hits = Cache::get('cache_hits', 0);
        $misses = Cache::get('cache_misses', 0);
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Record cache hit
     */
    public static function recordHit(): void
    {
        Cache::increment('cache_hits');
    }

    /**
     * Record cache miss
     */
    public static function recordMiss(): void
    {
        Cache::increment('cache_misses');
    }
}
