<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FinanceHrmModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'materialtrack']);
        config(['session.driver' => 'array']);
        DB::connection('mysql')->beginTransaction();

        $noop = new class(
            $this->app,
            $this->app->make(\Illuminate\Contracts\Encryption\Encrypter::class),
        ) extends PreventRequestForgery {
            public function handle($request, \Closure $next)
            {
                return $next($request);
            }
        };
        $this->app->instance(PreventRequestForgery::class, $noop);
        $this->app->instance(ValidateCsrfToken::class, $noop);
        $this->app->instance(VerifyCsrfToken::class, $noop);

        $this->actingAs($this->admin());
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();
        config(['database.default' => 'sqlite']);
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    private function coa(string $code, string $type = 'asset', string $subType = 'current_asset', bool $header = false): ChartOfAccount
    {
        return ChartOfAccount::create([
            'code' => $code.'-'.uniqid(),
            'name' => 'Akun Test '.$code,
            'type' => $type,
            'sub_type' => $subType,
            'is_header' => $header,
        ]);
    }

    public function test_chart_of_accounts_crud(): void
    {
        $this->get('/apps/chart-of-accounts')->assertOk();
        $this->get('/apps/chart-of-accounts/create')->assertOk();

        $header = $this->coa('HDR', 'asset', 'current_asset', true);

        $this->post('/apps/chart-of-accounts', [
            'code' => 'COA-TEST-'.uniqid(),
            'name' => 'Kas Test',
            'type' => 'asset',
            'sub_type' => 'current_asset',
            'parent_id' => $header->id,
            'is_header' => false,
            'opening_balance' => 1000000,
        ])->assertRedirect();
        $this->assertDatabaseHas('chart_of_accounts', ['name' => 'Kas Test']);

        $id = DB::table('chart_of_accounts')->where('name', 'Kas Test')->value('id');
        $this->get("/apps/chart-of-accounts/{$id}/edit")->assertOk();
        $this->put("/apps/chart-of-accounts/{$id}", [
            'code' => 'COA-TEST-'.uniqid(),
            'name' => 'Kas Test Updated',
            'type' => 'asset',
            'sub_type' => 'current_asset',
            'parent_id' => $header->id,
            'is_header' => false,
            'opening_balance' => 2000000,
            'is_active' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $id, 'name' => 'Kas Test Updated']);
    }

public function test_chart_of_account_destroy_akun_dipakai_jurnal_harus_tolak(): void
    {
        $account = $this->coa('USE');
        $other = $this->coa('CAP', 'equity', 'equity');

        $this->post('/apps/journal-entries', [
            'entry_date' => now()->format('Y-m-d'),
            'narration' => 'Test jurnal',
            'lines' => [
                ['chart_of_account_id' => $account->id, 'debit' => 1000, 'credit' => 0, 'memo' => 'Dr'],
                ['chart_of_account_id' => $other->id, 'debit' => 0, 'credit' => 1000, 'memo' => 'Cr'],
            ],
        ])->assertRedirect();

        $this->delete("/apps/chart-of-accounts/{$account->id}")->assertRedirect();
        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]);

        $free = $this->coa('FREE');
        $this->delete("/apps/chart-of-accounts/{$free->id}")->assertRedirect();
        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $free->id]);
    }

    public function test_journal_entry_balance_dan_show(): void
    {
        $this->get('/apps/journal-entries')->assertOk();
        $this->get('/apps/journal-entries/create')->assertOk();

        $dr = $this->coa('DR', 'expense', 'operating_expense');
        $cr = $this->coa('CR', 'equity', 'equity');

        $this->post('/apps/journal-entries', [
            'entry_date' => now()->format('Y-m-d'),
            'narration' => 'Jurnal tidak balance',
            'lines' => [
                ['chart_of_account_id' => $dr->id, 'debit' => 100, 'credit' => 0],
                ['chart_of_account_id' => $cr->id, 'debit' => 0, 'credit' => 90],
            ],
        ])->assertSessionHasErrors();
        $this->assertDatabaseMissing('journal_entries', ['narration' => 'Jurnal tidak balance']);

        $this->post('/apps/journal-entries', [
            'entry_date' => now()->format('Y-m-d'),
            'narration' => 'Jurnal balance',
            'lines' => [
                ['chart_of_account_id' => $dr->id, 'debit' => 100, 'credit' => 0],
                ['chart_of_account_id' => $cr->id, 'debit' => 0, 'credit' => 100],
            ],
        ])->assertRedirect();

        $entryId = DB::table('journal_entries')->where('narration', 'Jurnal balance')->value('id');
        $this->get("/apps/journal-entries/{$entryId}")->assertOk();
    }

public function test_financial_reports_halaman_renders(): void
    {
        $this->get('/apps/financial-reports')->assertOk();
        $this->get('/apps/financial-reports?tab=income-statement&from='.now()->startOfYear()->format('Y-m-d').'&to='.now()->format('Y-m-d'))->assertOk();
        $this->get('/apps/financial-reports?tab=balance-sheet')->assertOk();
    }

    public function test_cash_bank_crud_dan_jurnal_otomatis(): void
    {
        $this->get('/apps/cash-bank')->assertOk();
        $this->get('/apps/cash-bank/create')->assertOk();

        $cash = $this->coa('CASH', 'asset', 'current_asset');
        $partner = $this->coa('REV', 'revenue', 'operating_revenue');

        $this->post('/apps/cash-bank', [
            'transaction_date' => now()->format('Y-m-d'),
            'type' => 'in',
            'account_id' => $cash->id,
            'counter_account_id' => $partner->id,
            'amount' => 250000,
            'reference_no' => 'TRX-001',
            'description' => str_repeat('x', 200),
        ])->assertRedirect();

        $this->assertDatabaseHas('cash_bank_transactions', ['reference_no' => 'TRX-001']);
        $this->assertDatabaseHas('journal_entries', ['source' => 'cash_bank']);
    }

public function test_employee_crud(): void
    {
        $this->get('/apps/employees')->assertOk();
        $this->get('/apps/employees/create')->assertOk();

        $dept = Department::first() ?? Department::create(['name' => 'Dept Test '.uniqid()]);

        $this->post('/apps/employees', [
            'employee_id' => 'EMP-TEST-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Karyawan',
            'email' => 'test.karyawan@example.com',
            'department_id' => $dept->id,
            'position' => 'Staff Finance',
            'employment_status' => 'permanent',
            'hire_date' => '2025-01-01',
            'basic_salary' => 5000000,
        ])->assertRedirect();

        $id = DB::table('employees')->where('email', 'test.karyawan@example.com')->value('id');
        $this->get("/apps/employees/{$id}/edit")->assertOk();
        $this->put("/apps/employees/{$id}", [
            'employee_id' => 'EMP-TEST-'.uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Karyawan Update',
            'department_id' => $dept->id,
            'position' => 'Staff HR',
            'employment_status' => 'contract',
            'hire_date' => '2025-01-01',
            'basic_salary' => 6000000,
            'is_active' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('employees', ['id' => $id, 'position' => 'Staff HR']);

        $this->delete("/apps/employees/{$id}")->assertRedirect();
        $this->assertDatabaseMissing('employees', ['id' => $id]);
    }

    public function test_attendance_create_dan_dup_hari(): void
    {
        $this->get('/apps/attendances')->assertOk();
        $this->get('/apps/attendances/create')->assertOk();

        $employee = Employee::create([
            'employee_id' => 'EMP-ABS-'.uniqid(),
            'first_name' => 'Absen',
            'hire_date' => '2025-01-01',
        ]);

        $payload = [
            'employee_id' => $employee->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '08:00',
            'clock_out' => '17:00',
            'status' => 'present',
            'notes' => 'test',
        ];

        $this->post('/apps/attendances', $payload)->assertRedirect();
        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'status' => 'present']);

        // updateOrCreate: input ulang hari sama = update, bukan duplikat
        $payload['status'] = 'late';
        $payload['notes'] = 'terlambat';
        $this->post('/apps/attendances', $payload)->assertRedirect();
        $this->assertSame(1, Attendance::where('employee_id', $employee->id)->count());
        $this->assertDatabaseHas('attendances', ['employee_id' => $employee->id, 'status' => 'late']);
    }

public function test_leave_create_approve_reject(): void
    {
        $this->get('/apps/leaves')->assertOk();
        $this->get('/apps/leaves/create')->assertOk();

        $employee = Employee::create([
            'employee_id' => 'EMP-LV-'.uniqid(),
            'first_name' => 'Cuti',
            'hire_date' => '2025-01-01',
        ]);
        $leaveType = LeaveType::firstOrCreate(['name' => 'Cuti Tahunan Test '.uniqid()], ['default_days' => 12]);

        $this->post('/apps/leaves', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
            'reason' => 'Liburan keluarga',
        ])->assertRedirect();

        $leave = Leave::where('employee_id', $employee->id)->first();
        $this->assertSame(2, $leave->total_days);

        // Melebihi jatah
        $this->post('/apps/leaves', [
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(20)->format('Y-m-d'),
        ])->assertSessionHasErrors();

        $this->post("/apps/leaves/{$leave->id}/approve")->assertRedirect();
        $this->assertDatabaseHas('leaves', ['id' => $leave->id, 'status' => 'approved']);

        $p2 = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'total_days' => 1,
            'status' => 'pending',
        ]);
        $this->post("/apps/leaves/{$p2->id}/reject", ['rejection_reason' => 'Terlalu sering'])->assertRedirect();
        $this->assertDatabaseHas('leaves', ['id' => $p2->id, 'status' => 'rejected']);

        // approve status selain pending harus ditolak
        $this->post("/apps/leaves/{$p2->id}/approve")->assertSessionHasErrors('status');
    }

    public function test_payroll_lifecycle(): void
    {
        $this->get('/apps/payroll')->assertOk();
        $this->get('/apps/payroll/create')->assertOk();

        $employee = Employee::create([
            'employee_id' => 'EMP-PR-'.uniqid(),
            'first_name' => 'Gaji',
            'hire_date' => '2025-01-01',
            'basic_salary' => 7000000,
            'is_active' => true,
        ]);

        $name = 'Payroll Test '.uniqid();
        $this->post('/apps/payroll', [
            'name' => $name,
            'start_date' => now()->subYears(2)->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->subYears(2)->endOfMonth()->format('Y-m-d'),
            'pay_date' => now()->subYears(2)->endOfMonth()->format('Y-m-d'),
            'notes' => 'Periode uji',
        ])->assertRedirect();

        $period = PayrollPeriod::where('name', $name)->latest()->first();
        $this->assertNotNull($period);

        $this->get('/apps/payroll/'.$period->id)->assertOk();
        $this->assertSame(1, $period->items()->where('employee_id', $employee->id)->count());

        // approve: draft → approved
        $this->post('/apps/payroll/'.$period->id.'/approve')->assertRedirect();
        $this->assertDatabaseHas('payroll_periods', ['id' => $period->id, 'status' => 'approved']);

        // approve ulang harus ditolak (bukan draft)
        $this->post('/apps/payroll/'.$period->id.'/approve')->assertSessionHasErrors('status');

        // process: approved → paid
        $this->post('/apps/payroll/'.$period->id.'/process')->assertRedirect();
        $this->assertDatabaseHas('payroll_periods', ['id' => $period->id, 'status' => 'paid']);
    }
}