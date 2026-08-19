<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\StockOpname;

class StockOpnameObserver
{
    public function created(StockOpname $opname): void
    {
        // Opname dibuat sebagai pending; stok & movement hanya diterapkan
        // saat diverifikasi (lihat updated()).
        if ($opname->status !== 'verified') {
            return;
        }

        $this->recordMovement($opname);
    }

    public function updated(StockOpname $opname): void
    {
        // Movement 'adjustment' dibuat saat opname diverifikasi oleh
        // petugas kedua (stok sudah disesuaikan controller sebelum update).
        if ($opname->status === 'verified' && $opname->wasChanged('status')) {
            $this->recordMovement($opname);
        }
    }

    private function recordMovement(StockOpname $opname): void
    {
        if ($opname->difference === 0) {
            return;
        }

        StockMovement::create([
            'item_id' => $opname->item_id,
            'user_id' => $opname->verified_by ?? $opname->user_id,
            'type' => 'adjustment',
            'qty' => abs($opname->difference),
            'stock_before' => $opname->system_stock,
            'balance_after' => $opname->physical_stock,
            'ref_type' => StockOpname::class,
            'ref_id' => $opname->id,
            'notes' => 'Stock Opname '.$opname->period
                .($opname->difference > 0 ? ' (surplus)' : ' (defisit)'),
        ]);
    }
}
