<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model {
    protected $fillable = ['order_number', 'bom_id', 'item_id', 'qty_planned', 'qty_produced', 'start_date', 'end_date', 'status', 'notes'];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'qty_planned' => 'integer', 'qty_produced' => 'integer'];
    
    public function bom() { return $this->belongsTo(Bom::class); }
    public function item() { return $this->belongsTo(Item::class); }
}