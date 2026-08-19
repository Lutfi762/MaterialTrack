<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    protected $fillable = [
        'reference_no',
        'item_id',
        'from_department_id',
        'to_department_id',
        'qty',
        'unit',
        'status',
        'user_id',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateReferenceNo(): string
    {
        $prefix = 'TRF-' . now()->format('Ymd') . '-';
        $last = self::where('reference_no', 'like', $prefix . '%')
            ->orderByDesc('reference_no')
            ->value('reference_no');

        if ($last) {
            $seq = (int) substr($last, -4) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
