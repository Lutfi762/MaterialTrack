<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model {
    protected $fillable = ['item_id', 'code', 'name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    
    public function item() { return $this->belongsTo(Item::class); }
    public function items() { return $this->hasMany(BomItem::class); }
}