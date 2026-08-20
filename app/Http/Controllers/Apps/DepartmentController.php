<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('pics')
            ->latest()
            ->paginate(10);

        return Inertia::render('Apps/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Departments/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:departments,code',
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|in:active,inactive',
        ]);

        Department::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => strip_tags($request->description),
            'status'      => $request->status ?? 'active',
        ]);

        return to_route('apps.departments.index')->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        return Inertia::render('Apps/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $this->authorize('update', $department);
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string|max:500',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $department->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => strip_tags($request->description),
            'status'      => $request->status ?? $department->status,
        ]);

        return to_route('apps.departments.index')->with('success', 'Department berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);

        try {
            if ($department->pics()->exists() || $department->users()->exists() || $department->stockOuts()->exists()) {
                return back()->withErrors(['error' => 'Department tidak bisa dihapus karena masih memiliki data terkait (PIC, User, atau Transaksi).']);
            }

            $department->delete();

            return to_route('apps.departments.index')->with('success', 'Department berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus department.']);
        }
    }
}