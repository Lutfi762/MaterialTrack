<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssetMaintenance extends Model {
    protected $fillable = ['fixed_asset_id', 'maintenance_type', 'title', 'description', 'scheduled_date', 'completed_date', 'cost', 'performed_by', 'status'];
    protected $casts = ['scheduled_date' => 'date', 'completed_date' => 'date', 'cost' => 'decimal:2'];
    public function fixedAsset() { return $this->belongsTo(FixedAsset::class); }
}