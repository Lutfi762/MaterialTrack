<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FleetDeliveryOrder extends Model {
    protected $guarded = [];
    protected $casts = ['delivery_date' => 'date'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function vehicle() { return $this->belongsTo(FleetVehicle::class, 'fleet_vehicle_id'); }
    public function driver() { return $this->belongsTo(User::class, 'driver_id'); }
}