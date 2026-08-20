<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                    ->orWhere('last_name', 'like', "%{$request->search}%")
                    ->orWhere('employee_id', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return Inertia::render('Apps/Employees/Index', [
            'employees' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Employees/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:100',
            'employment_status' => 'required|in:permanent,contract,probation,intern,resigned',
            'hire_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
        ]);

        Employee::create($validated);

        return to_route('apps.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Apps/Employees/Edit', [
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:50|unique:employees,employee_id,' . $employee->id,
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:100',
            'employment_status' => 'required|in:permanent,contract,probation,intern,resigned',
            'hire_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $employee->update($validated);

        return to_route('apps.employees.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->attendances()->exists() || $employee->leaves()->exists()
            || $employee->contracts()->exists() || $employee->payrollItems()->exists()) {
            return back()->with('error', 'Karyawan memiliki riwayat presensi/cuti/kontrak/payroll — nonaktifkan saja, jangan dihapus.');
        }

        $employee->delete();
        return to_route('apps.employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
