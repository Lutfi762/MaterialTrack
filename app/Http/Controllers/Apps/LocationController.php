<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Location;
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
            'name'           => 'required|string|max:255',
            'warehouse_zone' => 'nullable|string|max:100',
        ]);

        Location::create($request->only('name', 'warehouse_zone'));

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
            'name'           => 'required|string|max:255',
            'warehouse_zone' => 'nullable|string|max:100',
        ]);

        $location->update($request->only('name', 'warehouse_zone'));

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return to_route('apps.locations.index')->with('success', 'Lokasi gudang berhasil dihapus.');
    }
}