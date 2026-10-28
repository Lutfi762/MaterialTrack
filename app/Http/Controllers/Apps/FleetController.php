<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\{FleetVehicle, FleetDeliveryOrder, Customer, User};
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetController extends Controller {
    public function vehicles(Request $request) {
        $query = FleetVehicle::query();
        if($request->search) $query->where('license_plate', 'like', "%{$request->search}%");
        return Inertia::render('Apps/Fleet/Vehicles', ['vehicles' => $query->paginate(15)->withQueryString()]);
    }
    public function storeVehicle(Request $request) {
        $v = $request->validate(['license_plate'=>'required|unique:fleet_vehicles', 'vehicle_type'=>'required', 'brand_model'=>'required', 'capacity_kg'=>'nullable|numeric|min:0', 'status'=>'required|in:available,in_use,maintenance,retired']);
        FleetVehicle::create($v);
        return back()->with('success', 'Vehicle added');
    }
    public function updateVehicle(Request $request, FleetVehicle $vehicle) {
        $vehicle->update($request->validate(['status'=>'required|in:available,in_use,maintenance,retired']));
        return back();
    }

    public function deliveries(Request $request) {
        $query = FleetDeliveryOrder::with(['customer', 'vehicle', 'driver']);
        if($request->search) $query->where('do_number', 'like', "%{$request->search}%");
        return Inertia::render('Apps/Fleet/Deliveries', [
            'deliveries' => $query->paginate(15)->withQueryString(),
            'customers' => Customer::all(['id','name']),
            'vehicles' => FleetVehicle::where('status', 'available')->get(),
            'users' => User::all(['id','name'])
        ]);
    }
    public function storeDelivery(Request $request) {
        $v = $request->validate(['customer_id'=>'required|exists:customers,id', 'fleet_vehicle_id'=>'nullable|exists:fleet_vehicles,id', 'driver_id'=>'nullable|exists:users,id', 'delivery_date'=>'required|date', 'delivery_address'=>'required', 'status'=>'required|in:draft,scheduled,in_transit,delivered,cancelled', 'notes'=>'nullable']);
        \Illuminate\Support\Facades\DB::transaction(function () use ($v) {
            $v['do_number'] = DocumentNumber::next('fleet_delivery_orders', 'DO');
            FleetDeliveryOrder::create($v);
        });
        return back()->with('success', 'Delivery Order created');
    }
    public function updateDeliveryStatus(Request $request, FleetDeliveryOrder $delivery) {
        $delivery->update($request->validate(['status'=>'required|in:draft,scheduled,in_transit,delivered,cancelled']));
        return back();
    }
}