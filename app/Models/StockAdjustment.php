<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'item_id', 'user_id', 'qty', 'reason',
        'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'qty' => 'integer',
        'approved_at' => 'datetime',
    ];

    public static function generateReferenceNo(): string
    {
        $prefix = 'ADJ-'.now()->format('Ymd').'-';

        $last = static::where('reference_no', 'like', $prefix.'%')
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $next = $last ? ((int) substr($last, -4) + 1) : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
