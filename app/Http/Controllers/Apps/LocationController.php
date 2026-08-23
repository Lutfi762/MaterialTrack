<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Item;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Location::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $locations = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Apps/Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Locations/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:locations,code',
            'warehouse_zone' => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:500',
        ]);

        Location::create($request->only('name', 'code', 'warehouse_zone', 'address'));

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        $this->authorize('update', $location);

        return Inertia::render('Apps/Locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $this->authorize('update', $location);
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:locations,code,' . $location->id,
            'warehouse_zone' => 'nullable|string|max:100',
            'address'        => 'nullable|string|max:500',
        ]);

        $location->update($request->only('name', 'code', 'warehouse_zone', 'address'));

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        $this->authorize('delete', $location);

        try {
            // Lepaskan material dari lokasi (FK nullOnDelete) supaya hapus lokasi tetap bisa
            $location->items()->update(['location_id' => null]);

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
            ->get(['id', 'name', 'code', 'warehouse_zone', 'address']);

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