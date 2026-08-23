<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::withCount('items');

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $warehouses = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Apps/Warehouses/Index', [
            'warehouses' => $warehouses,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Warehouses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        Warehouse::create($validated);

        return to_route('apps.warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function edit(Warehouse $warehouse)
    {
        return Inertia::render('Apps/Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return to_route('apps.warehouses.index')->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            // Lepaskan material dari gudang (FK nullOnDelete) supaya hapus gudang tetap bisa
            $warehouse->items()->update(['warehouse_id' => null]);

            $warehouse->delete();

            return to_route('apps.warehouses.index')->with('success', 'Gudang berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus gudang.']);
        }
    }
}
