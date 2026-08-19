<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id', 'user_id', 'pic_id', 'type', 'qty',
        'stock_before', 'balance_after',
        'ref_type', 'ref_id', 'notes', 'hash',
    ];

    protected static function booted(): void
    {
        // Hash berantai: setiap baris menautkan hash baris sebelumnya,
        // sehingga ledger tidak bisa diubah tanpa memutus rantai.
        static::creating(function (StockMovement $movement) {
            $movement->created_at = $movement->created_at ?? now();
            $movement->hash = static::computeHash(
                static::query()->orderByDesc('id')->value('hash'),
                $movement->getAttributes()
            );
        });
    }

    public static function computeHash(?string $prevHash, array $data): string
    {
        $payload = implode('|', [
            $data['item_id'] ?? '',
            $data['user_id'] ?? '',
            $data['pic_id'] ?? '',
            $data['type'] ?? '',
            $data['qty'] ?? '',
            $data['stock_before'] ?? '',
            $data['balance_after'] ?? '',
            $data['ref_type'] ?? '',
            $data['ref_id'] ?? '',
            $data['notes'] ?? '',
            $data['created_at'] ?? '',
        ]);

        return hash('sha256', ($prevHash ?: 'GENESIS').'|'.$payload);
    }

    public static function verifyIntegrity(): array
    {
        $prevHash = null;
        $checked = 0;
        $valid = true;
        $brokenIds = [];

        static::query()->orderBy('id')->chunkById(500, function ($rows) use (&$prevHash, &$checked, &$valid, &$brokenIds) {
            foreach ($rows as $row) {
                $expected = static::computeHash($prevHash, $row->getAttributes());
                $prevHash = $row->hash;
                $checked++;

                if ($row->hash !== $expected) {
                    $valid = false;
                    $brokenIds[] = $row->id;
                }
            }
        });

        return [
            'valid' => $valid,
            'checked' => $checked,
            'broken_ids' => $brokenIds,
        ];
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('d-M-Y H:i:s'),
        );
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }
}
