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
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive',
        ]);

        Department::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'status'      => $request->status ?? 'active',
        ]);

        return to_route('apps.departments.index')->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit(Department $department)
    {
        return Inertia::render('Apps/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'status'      => 'nullable|in:active,inactive',
        ]);

        $department->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
            'status'      => $request->status ?? $department->status,
        ]);

        return to_route('apps.departments.index')->with('success', 'Department berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return to_route('apps.departments.index')->with('success', 'Department berhasil dihapus.');
    }
}