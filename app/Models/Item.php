<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $current_stock
 * @property int $min_stock
 * @property string $name
 * @property string $code
 * @property string $unit
 */
class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'location_id', 'code', 'name', 'description',
        'unit', 'min_stock', 'current_stock', 'image',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) return 'habis';
        if ($this->current_stock <= $this->min_stock) return 'menipis';
        return 'aman';
    }
}