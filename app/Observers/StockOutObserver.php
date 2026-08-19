<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockOut;

class StockOutObserver
{
    public function created(StockOut $stockOut): void
    {
        // Stock Out pending belum mengurangi stok, jadi movement baru dibuat
        // saat status berubah menjadi approved (lihat updated()).
        if ($stockOut->status !== 'approved') {
            return;
        }

        $this->recordMovement($stockOut);
    }

    public function updated(StockOut $stockOut): void
    {
        // Movement 'out' dibuat saat transaksi pending disetujui.
        // Saat itu stok sudah dikurangi oleh controller/approve().
        if ($stockOut->status === 'approved' && $stockOut->wasChanged('status')) {
            $this->recordMovement($stockOut);
        }
    }

    private function recordMovement(StockOut $stockOut): void
    {
        /** @var Item $item */
        $item = $stockOut->item;
        $stockBefore = $item->current_stock + $stockOut->qty;

        StockMovement::create([
            'item_id' => $stockOut->item_id,
            'user_id' => $stockOut->user_id,
            'type' => 'out',
            'qty' => $stockOut->qty,
            'stock_before' => $stockBefore,
            'balance_after' => $item->current_stock,
            'ref_type' => StockOut::class,
            'ref_id' => $stockOut->id,
            'notes' => $stockOut->notes ?? null,
        ]);
    }
}
