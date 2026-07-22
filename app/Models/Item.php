<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

 protected $fillable = [
    'category_id',
    'location_id',
    'code',
    'name',
    'description',
    'unit',
    'min_stock',
    'current_stock',
    'image',
];
    /**
     * Accessor: image → full URL ke storage
     */
    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? asset('/storage/items/' . $value) : null,
        );
    }

    /**
     * Accessor: format tanggal created_at
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('d-M-Y H:i:s'),
        );
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}