<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QualityControl extends Model {
    protected $fillable = ['qc_number', 'reference_number', 'item_id', 'qty_inspected', 'qty_passed', 'qty_rejected', 'inspector_name', 'status', 'notes'];
    public function item() { return $this->belongsTo(Item::class); }
}