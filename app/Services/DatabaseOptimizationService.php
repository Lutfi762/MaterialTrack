<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

/**
 * Database Optimization Service untuk MaterialTrack System
 * Optimasi untuk ratusan concurrent users
 */
class DatabaseOptimizationService
{
    /**
     * Critical indexes untuk performa tinggi
     */
    const CRITICAL_INDEXES = [
        'items' => [
            'idx_items_current_stock_min' => 'CREATE INDEX idx_items_current_stock_min ON items(current_stock, min_stock)',
            'idx_items_category_department' => 'CREATE INDEX idx_items_category_department ON items(category_id, id)',
            'idx_items_code_search' => 'CREATE INDEX idx_items_code_search ON items(code, name(50))',
        ],
        'stock_movements' => [
            'idx_stock_movements_item_date' => 'CREATE INDEX idx_stock_movements_item_date ON stock_movements(item_id, created_at DESC)',
            'idx_stock_movements_type_date' => 'CREATE INDEX idx_stock_movements_type_date ON stock_movements(type, created_at)',
            'idx_stock_movements_user_item' => 'CREATE INDEX idx_stock_movements_user_item ON stock_movements(user_id, item_id)',
        ],
        'stock_outs' => [
            'idx_stock_outs_department_date' => 'CREATE INDEX idx_stock_outs_department_date ON stock_outs(department_id, date)',
            'idx_stock_outs_item_date' => 'CREATE INDEX idx_stock_outs_item_date ON stock_outs(item_id, date)',
            'idx_stock_outs_date_qty' => 'CREATE INDEX idx_stock_outs_date_qty ON stock_outs(date, qty)',
        ],
        'stock_ins' => [
            'idx_stock_ins_department_date' => 'CREATE INDEX idx_stock_ins_department_date ON stock_ins(department_id, date)',
            'idx_stock_ins_item_date' => 'CREATE INDEX idx_stock_ins_item_date ON stock_ins(item_id, date)',
        ],
        'category_department' => [
            'idx_category_department_department' => 'CREATE INDEX idx_category_department_department ON category_department(department_id, category_id)',
            'idx_category_department_category' => 'CREATE INDEX idx_category_department_category ON category_department(category_id, department_id)',
        ],
        'users' => [
            'idx_users_department_role' => 'CREATE INDEX idx_users_department_role ON users(department_id, id)',
            'idx_users_email_department' => 'CREATE INDEX idx_users_email_department ON users(email, department_id)',
        ],
    ];

    /**
     * Optimize semua critical queries
     */
    public static function optimizeAllQueries(): array
    {
        $results = [
            'indexes' => self::createMissingIndexes(),
            'analyze_tables' => self::analyzeTables(),
            'optimize_tables' => self::optimizeTables(),
            'query_cache' => self::optimizeQueryCache(),
        ];

        return $results;
    }

    /**
     * Create missing critical indexes
     */
    public static function createMissingIndexes(): array
    {
        $created = [];
        $skipped = [];
        $errors = [];

        foreach (self::CRITICAL_INDEXES as $table => $indexes) {
            if (!Schema::hasTable($table)) {
                $skipped[$table] = "Table {$table} tidak ditemukan";
                continue;
            }

            foreach ($indexes as $indexName => $createStatement) {
                try {
                    // Check if index exists
                    $indexExists = DB::select("
                        SELECT COUNT(*) as count 
                        FROM information_schema.statistics 
                        WHERE table_schema = DATABASE() 
                        AND table_name = '{$table}' 
                        AND index_name = '{$indexName}'
                    ")[0]->count > 0;

                    if (!$indexExists) {
                        DB::statement($createStatement);
                        $created[] = "{$table}.{$indexName}";
                    } else {
                        $skipped[] = "{$table}.{$indexName} (sudah ada)";
                    }
                } catch (QueryException $e) {
                    $errors[] = "Gagal create index {$table}.{$indexName}: " . $e->getMessage();
                }
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_created' => count($created),
            'total_skipped' => count($skipped),
        ];
    }

    /**
     * Analyze table statistics untuk query optimization
     */
    public static function analyzeTables(): array
    {
        $tables = [
            'items', 'stock_movements', 'stock_outs', 'stock_ins',
            'categories', 'departments', 'category_department', 'users'
        ];

        $results = [];
        
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                DB::statement("ANALYZE TABLE {$table}");
                
                $tableStats = DB::select("
                    SELECT 
                        table_name,
                        table_rows,
                        avg_row_length,
                        data_length,
                        index_length,
                        (data_length + index_length) as total_size,
                        (index_length / NULLIF(data_length, 0)) * 100 as index_efficiency
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = '{$table}'
                ")[0] ?? null;

                if ($tableStats) {
                    $results[$table] = [
                        'rows' => number_format($tableStats->table_rows),
                        'avg_row_length' => number_format($tableStats->avg_row_length) . ' bytes',
                        'data_size' => self::formatBytes($tableStats->data_length),
                        'index_size' => self::formatBytes($tableStats->index_length),
                        'total_size' => self::formatBytes($tableStats->total_size),
                        'index_efficiency' => round($tableStats->index_efficiency, 2) . '%',
                    ];
                }
            } catch (QueryException $e) {
                $results[$table] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Optimize tables (defragment)
     */
    public static function optimizeTables(): array
    {
        $tables = [
            'items', 'stock_movements', 'stock_outs', 'stock_ins',
            'categories', 'departments', 'category_department', 'users'
        ];

        $results = [];
        
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            try {
                $before = DB::select("SHOW TABLE STATUS LIKE '{$table}'")[0];
                DB::statement("OPTIMIZE TABLE {$table}");
                $after = DB::select("SHOW TABLE STATUS LIKE '{$table}'")[0];

                $results[$table] = [
                    'before_data_free' => self::formatBytes($before->Data_free),
                    'after_data_free' => self::formatBytes($after->Data_free),
                    'freed_space' => self::formatBytes($before->Data_free - $after->Data_free),
                    'status' => 'optimized',
                ];
            } catch (QueryException $e) {
                $results[$table] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Format bytes to human readable
     */
    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}