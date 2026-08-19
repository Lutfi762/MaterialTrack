<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use Illuminate\Support\Facades\DB;

/**
 * Stock Transaction Service untuk MaterialTrack System
 * Handle concurrent stock updates dengan transaction safety
 */
class StockTransactionService
{
    /**
     * Process stock in dengan transaction safety
     */
    public static function processStockIn(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Lock item untuk prevent concurrent updates
            $item = DB::table('items')
                ->where('id', $data['item_id'])
                ->lockForUpdate()
                ->first();

            if (! $item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // 2. Calculate new stock dengan atomic operation
            $newStock = $item->current_stock + $data['qty'];

            // 3. Update item stock secara atomic
            DB::table('items')
                ->where('id', $data['item_id'])
                ->update([
                    'current_stock' => $newStock,
                    'updated_at' => now(),
                ]);

            // 4. Create stock in record (observer akan membuat stock movement otomatis)
            $stockIn = StockIn::create([
                'item_id' => $data['item_id'],
                'department_id' => $data['department_id'] ?? null,
                'user_id' => $userId,
                'qty' => $data['qty'],
                'date' => $data['date'] ?? now(),
                'reference_no' => $data['reference'] ?? $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // 5. Update cache
            CacheService::clearDepartmentCache($data['department_id'] ?? null);

            return [
                'success' => true,
                'stock_in' => $stockIn,
                'item' => Item::find($data['item_id']),
                'old_stock' => $item->current_stock,
                'new_stock' => $newStock,
            ];
        });
    }

    /**
     * Process stock out dengan transaction safety
     */
    public static function processStockOut(array $data, int $userId): array
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Lock item untuk prevent concurrent updates
            $item = DB::table('items')
                ->where('id', $data['item_id'])
                ->lockForUpdate()
                ->first();

            if (! $item) {
                throw new \Exception('Item tidak ditemukan');
            }

            // 2. Validate sufficient stock
            if ($item->current_stock < $data['qty']) {
                throw new \Exception("Stock tidak mencukupi. Stock tersedia: {$item->current_stock}");
            }

            // 3. Calculate new stock dengan atomic operation
            $newStock = $item->current_stock - $data['qty'];

            // 4. Update item stock secara atomic
            DB::table('items')
                ->where('id', $data['item_id'])
                ->update([
                    'current_stock' => $newStock,
                    'updated_at' => now(),
                ]);

            // 5. Create stock out record (observer akan membuat stock movement otomatis)
            $stockOut = StockOut::create([
                'item_id' => $data['item_id'],
                'department_id' => $data['department_id'] ?? null,
                'user_id' => $userId,
                'qty' => $data['qty'],
                'date' => $data['date'] ?? now(),
                'reference_no' => $data['reference'] ?? $data['reference_no'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // 6. Update cache
            CacheService::clearDepartmentCache($data['department_id'] ?? null);

            return [
                'success' => true,
                'stock_out' => $stockOut,
                'item' => Item::find($data['item_id']),
                'old_stock' => $item->current_stock,
                'new_stock' => $newStock,
            ];
        });
    }

    /**
     * Batch process multiple stock ins
     */
    public static function batchStockIn(array $items, int $userId): array
    {
        $results = [];

        foreach ($items as $itemData) {
            try {
                $result = self::processStockIn($itemData, $userId);
                $results[] = [
                    'item_id' => $itemData['item_id'],
                    'success' => true,
                    'result' => $result,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'item_id' => $itemData['item_id'],
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_items' => count($items),
            'successful' => count(array_filter($results, fn ($r) => $r['success'])),
            'failed' => count(array_filter($results, fn ($r) => ! $r['success'])),
            'results' => $results,
        ];
    }

    /**
     * Batch process multiple stock outs
     */
    public static function batchStockOut(array $items, int $userId): array
    {
        $results = [];

        foreach ($items as $itemData) {
            try {
                $result = self::processStockOut($itemData, $userId);
                $results[] = [
                    'item_id' => $itemData['item_id'],
                    'success' => true,
                    'result' => $result,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'item_id' => $itemData['item_id'],
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_items' => count($items),
            'successful' => count(array_filter($results, fn ($r) => $r['success'])),
            'failed' => count(array_filter($results, fn ($r) => ! $r['success'])),
            'results' => $results,
        ];
    }
}
