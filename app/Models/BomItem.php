<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model {
    protected $fillable = ['bom_id', 'item_id', 'quantity'];
    protected $casts = ['quantity' => 'decimal:2'];
    
    public function bom() { return $this->belongsTo(Bom::class); }
    public function item() { return $this->belongsTo(Item::class); }
}