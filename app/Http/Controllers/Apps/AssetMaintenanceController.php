<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\AssetMaintenance;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AssetMaintenanceController extends Controller {
    public function index(Request $request) {
        $query = AssetMaintenance::with('fixedAsset');
        if ($request->status) $query->where('status', $request->status);
        return Inertia::render('Apps/AssetMaintenances/Index', [
            'maintenances' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status')
        ]);
    }
    public function create() {
        return Inertia::render('Apps/AssetMaintenances/Create', [
            'assets' => FixedAsset::where('status', 'active')->get()
        ]);
    }
    public function store(Request $request) {
        $v = $request->validate([
            'fixed_asset_id' => 'required|exists:fixed_assets,id',
            'maintenance_type' => 'required|in:preventive,corrective',
            'title' => 'required|string',
            'scheduled_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|string'
        ]);
        AssetMaintenance::create($v);
        return to_route('apps.asset-maintenances.index')->with('success', 'Maintenance scheduled');
    }
    public function edit(AssetMaintenance $assetMaintenance) {
        return Inertia::render('Apps/AssetMaintenances/Edit', [
            'maintenance' => $assetMaintenance,
            'assets' => FixedAsset::all()
        ]);
    }
    public function update(Request $request, AssetMaintenance $assetMaintenance) {
        $v = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'completed_date' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'performed_by' => 'nullable|string'
        ]);
        $assetMaintenance->update($v);
        return to_route('apps.asset-maintenances.index')->with('success', 'Maintenance updated');
    }
    public function destroy(AssetMaintenance $assetMaintenance) {
        $assetMaintenance->delete();
        return back()->with('success', 'Maintenance deleted');
    }
}