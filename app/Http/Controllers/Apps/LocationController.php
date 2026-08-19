<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::latest()->paginate(10);

        return Inertia::render('Apps/Locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Locations/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:50|unique:locations,code',
            'address' => 'nullable|string|max:500',
        ]);

        Location::create($request->only('name', 'code', 'address'));

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return Inertia::render('Apps/Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:50|unique:locations,code,' . $location->id,
            'address' => 'nullable|string|max:500',
        ]);

        $location->update($request->only('name', 'code', 'address'));

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        try {
            if ($location->items()->exists()) {
                return back()->withErrors(['error' => 'Lokasi tidak bisa dihapus karena masih memiliki material.']);
            }

            $location->delete();

            return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus lokasi gudang.']);
        }
    }

    public function map()
    {
        $locations = Location::withCount('items as item_count')
            ->withSum('items as total_stock', 'current_stock')
            ->orderBy('code')
            ->get(['id', 'name', 'code', 'address']);

        $lowStockItems = Item::whereColumn('current_stock', '<=', 'min_stock')
            ->whereNotNull('location_id')
            ->select('location_id')
            ->count();

        return Inertia::render('Apps/Locations/Map', [
            'locations' => $locations,
            'low_stock_at_location' => $lowStockItems,
        ]);
    }
}