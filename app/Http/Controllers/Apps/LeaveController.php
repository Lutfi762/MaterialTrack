<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with(['employee', 'leaveType', 'approver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Apps/Leaves/Index', [
            'leaves' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Leaves/Create', [
            'employees' => Employee::where('is_active', true)->get(['id', 'first_name', 'last_name', 'employee_id']),
            'leaveTypes' => LeaveType::orderBy('name')->get(['id', 'name', 'default_days']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $start = new \DateTime($validated['start_date']);
        $end = new \DateTime($validated['end_date']);
        $totalDays = $end->diff($start)->days + 1;

        // Jatah cuti tidak boleh dilampaui (default_days per jenis cuti)
        $leaveType = LeaveType::find($validated['leave_type_id']);
        if ($totalDays > $leaveType->default_days) {
            return back()->withErrors(['end_date' => "Total hari cuti ({$totalDays}) melebihi jatah {$leaveType->name} ({$leaveType->default_days} hari)."]);
        }

        Leave::create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return to_route('apps.leaves.index')->with('success', 'Pengajuan cuti berhasil didaftarkan.');
    }

    public function approve(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya pengajuan berstatus pending yang dapat disetujui.']);
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Pengajuan cuti disetujui.');
    }

    public function reject(Request $request, Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->withErrors(['status' => 'Hanya pengajuan berstatus pending yang dapat ditolak.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }
}
