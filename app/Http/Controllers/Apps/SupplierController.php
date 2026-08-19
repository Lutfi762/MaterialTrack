<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('is_active') && $request->is_active !== '' && $request->is_active !== null) {
            $query->where('is_active', $request->is_active);
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Apps/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Suppliers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:suppliers,code',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:1000',
            'lead_time_days' => 'nullable|integer|min:0',
            'rating'         => 'nullable|numeric|min:0|max:5',
            'notes'          => 'nullable|string|max:1000',
            'is_active'      => 'boolean',
        ]);

        Supplier::create($request->only(
            'name', 'code', 'contact_person', 'email', 'phone',
            'address', 'lead_time_days', 'rating', 'notes', 'is_active'
        ));

        return to_route('apps.suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Apps/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50|unique:suppliers,code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:1000',
            'lead_time_days' => 'nullable|integer|min:0',
            'rating'         => 'nullable|numeric|min:0|max:5',
            'notes'          => 'nullable|string|max:1000',
            'is_active'      => 'boolean',
        ]);

        $supplier->update($request->only(
            'name', 'code', 'contact_person', 'email', 'phone',
            'address', 'lead_time_days', 'rating', 'notes', 'is_active'
        ));

        return to_route('apps.suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();

            return to_route('apps.suppliers.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus supplier.']);
        }
    }
}
