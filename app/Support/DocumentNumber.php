<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Pembuat nomor dokumen bebas race condition (count()+1 tidak aman).
 * WAJIB dipanggil DI DALAM DB::transaction — gap lock pada id terakhir
 * membuat alokasi nomor berurutan dan unik antar request konkuren.
 */
class DocumentNumber
{
    public static function next(string $table, string $prefix, int $pad = 5): string
    {
        $lastId = (int) DB::table($table)->orderByDesc('id')->lockForUpdate()->value('id');

        return $prefix . '-' . date('Ym') . '-' . str_pad($lastId + 1, $pad, '0', STR_PAD_LEFT);
    }
}