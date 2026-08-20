<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee')->latest('date');

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        return Inertia::render('Apps/Attendances/Index', [
            'attendances' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['date']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Attendances/Create', [
            'employees' => Employee::where('is_active', true)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:present,late,absent,sick,leave,holiday',
            'notes' => 'nullable|string',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        return to_route('apps.attendances.index')->with('success', 'Presensi karyawan berhasil dicatat.');
    }
}
