<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollPeriod::latest();

        if ($request->filled('status') && in_array($request->status, ['draft', 'approved', 'processing', 'paid', 'completed', 'closed'], true)) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Apps/Payroll/Index', [
            'periods' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Payroll/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pay_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Larang periode gaji tumpang tindih (satu karyawan tidak boleh digaji 2x di periode sama)
        $overlap = PayrollPeriod::where('start_date', '<=', $validated['end_date'])
            ->where('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Periode ini tumpang tindih dengan periode penggajian yang sudah ada.']);
        }

        DB::transaction(function () use ($validated) {
            $period = PayrollPeriod::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'pay_date' => $validated['pay_date'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0,
            ]);

            // Generate payroll item untuk seluruh karyawan aktif
            $employees = Employee::where('is_active', true)->get();
            $totalAmount = 0;

            foreach ($employees as $employee) {
                $basic = $employee->basic_salary ?? 0;
                $net = $basic; // Bisa ditambahkan kalkulasi tunjangan & potongan
                $totalAmount += $net;

                $period->items()->create([
                    'employee_id' => $employee->id,
                    'basic_salary' => $basic,
                    'allowances' => 0,
                    'deductions' => 0,
                    'net_salary' => $net,
                ]);
            }

            $period->update(['total_amount' => $totalAmount]);
        });

        return to_route('apps.payroll.index')->with('success', 'Periode penggajian berhasil dibuat dan draf komponen gaji dihitung.');
    }

    public function show(PayrollPeriod $payroll)
    {
        return Inertia::render('Apps/Payroll/Show', [
            'period' => $payroll->load('items.employee'),
        ]);
    }

    public function approve(PayrollPeriod $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->withErrors(['status' => 'Hanya periode berstatus draft yang dapat disetujui (saat ini: ' . $payroll->status . ').']);
        }

        $payroll->update(['status' => 'approved']);
        return back()->with('success', 'Periode penggajian disetujui.');
    }

    public function process(PayrollPeriod $payroll)
    {
        if ($payroll->status !== 'approved') {
            return back()->withErrors(['status' => 'Hanya periode berstatus approved yang dapat diproses (saat ini: ' . $payroll->status . ').']);
        }

        $payroll->update(['status' => 'paid']);
        return back()->with('success', 'Penggajian berhasil diproses dan dibayarkan.');
    }
}
