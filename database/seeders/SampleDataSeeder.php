<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Location;
use App\Models\Pic;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CompanyProfile;
use App\Models\ChartOfAccount;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\ApprovalWorkflow;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Locations
        $locMain = Location::firstOrCreate(['code' => 'LOC-A1'], ['name' => 'Rak A1 - Utama', 'address' => 'Gudang Utama']);
        $locSub  = Location::firstOrCreate(['code' => 'LOC-B2'], ['name' => 'Rak B2 - Cadangan', 'address' => 'Gudang B']);

        // 2. Warehouses
        $whMain = Warehouse::firstOrCreate(['code' => 'WH-01'], ['name' => 'Gudang Pusat', 'address' => 'Jl. Industri No. 1', 'is_active' => true]);
        $whSub  = Warehouse::firstOrCreate(['code' => 'WH-02'], ['name' => 'Gudang Bahan Baku', 'address' => 'Jl. Industri No. 2', 'is_active' => true]);

        // Suppliers & Customers
        Supplier::firstOrCreate(['code' => 'SUP-001'], ['name' => 'PT Supplier Utama', 'contact_person' => 'Budi Suparman', 'phone' => '081122334455', 'email' => 'supplier@utama.com', 'address' => 'Jakarta']);
        Customer::firstOrCreate(['code' => 'CUST-001'], ['name' => 'PT Mitra Sejahtera', 'contact_person' => 'Andi Wijaya', 'phone' => '083344556677', 'email' => 'andi@mitrasejahtera.com', 'address' => 'Bandung', 'credit_limit' => 50000000]);

        // Company Profile
        CompanyProfile::firstOrCreate(['email' => 'info@materialtrack.com'], [
            'name' => 'PT Material Track Indonesia',
            'phone' => '021-5551234',
            'address' => 'Kawasan Industri Block C No. 10, Cikarang',
            'website' => 'https://materialtrack.local',
            'tax_id' => '01.234.567.8-901.000'
        ]);

        // COA
        $coa1 = ChartOfAccount::firstOrCreate(['code' => '1000'], ['name' => 'Kas & Bank', 'type' => 'asset', 'sub_type' => 'current_asset', 'is_header' => true]);
        ChartOfAccount::firstOrCreate(['code' => '1001'], ['name' => 'Kas Kecil', 'type' => 'asset', 'sub_type' => 'current_asset', 'parent_id' => $coa1->id, 'opening_balance' => 5000000]);
        ChartOfAccount::firstOrCreate(['code' => '1002'], ['name' => 'Bank BCA', 'type' => 'asset', 'sub_type' => 'current_asset', 'parent_id' => $coa1->id, 'opening_balance' => 150000000]);
        // Penyeimbang saldo awal (modal): bernilai negatif karena konvensi debit-positive
        // (aset +155jt -> ekuitas -155jt) agar neraca saldo tetap balance.
        ChartOfAccount::firstOrCreate(['code' => '3001'], ['name' => 'Modal / Saldo Awal', 'type' => 'equity', 'sub_type' => 'equity', 'opening_balance' => -155000000]);

        // 6. Employees & Workflows
        $deptIt = Department::where('code', 'DEPT-IT')->first();
        $deptPrd = Department::where('code', 'DEPT-PRD')->first();
        $deptQc = Department::where('code', 'DEPT-QC')->first();

        Employee::firstOrCreate(['employee_id' => 'EMP-001'], ['first_name' => 'Budi', 'last_name' => 'Santoso', 'email' => 'budi.emp@materialtrack.local', 'phone' => '08123456789', 'department_id' => $deptIt?->id, 'position' => 'IT Support Specialist', 'hire_date' => '2025-01-01', 'employment_status' => 'permanent', 'basic_salary' => 7500000]);
        Employee::firstOrCreate(['employee_id' => 'EMP-002'], ['first_name' => 'Ahmad', 'last_name' => 'Dahlan', 'email' => 'ahmad.emp@materialtrack.local', 'phone' => '08123456780', 'department_id' => $deptPrd?->id, 'position' => 'Supervisor Produksi', 'hire_date' => '2025-01-01', 'employment_status' => 'permanent', 'basic_salary' => 8500000]);

        // 8. Leave Types (dibutuhkan dropdown Leaves/Create)
        LeaveType::firstOrCreate(['name' => 'Cuti Tahunan'], ['default_days' => 12, 'is_paid' => true]);
        LeaveType::firstOrCreate(['name' => 'Cuti Sakit'], ['default_days' => 14, 'is_paid' => true]);
        LeaveType::firstOrCreate(['name' => 'Cuti Melahirkan'], ['default_days' => 90, 'is_paid' => true]);
        LeaveType::firstOrCreate(['name' => 'Cuti Tanpa Gaji'], ['default_days' => 0, 'is_paid' => false]);

        ApprovalWorkflow::firstOrCreate(['module' => 'purchase_request'], ['name' => 'Alur Persetujuan PR Standard', 'min_amount' => 5000000, 'is_active' => true]);
        ApprovalWorkflow::firstOrCreate(['module' => 'leave'], ['name' => 'Alur Persetujuan Cuti', 'min_amount' => 0, 'is_active' => true]);

        if ($deptIt) {
            Pic::firstOrCreate(['nik' => 'PIC001'], ['name' => 'Budi Santoso', 'phone' => '08123456789', 'email' => 'budi@example.com', 'department_id' => $deptIt->id]);
        }
        if ($deptPrd) {
            Pic::firstOrCreate(['nik' => 'PIC002'], ['name' => 'Ahmad Dahlan', 'phone' => '08123456780', 'email' => 'ahmad@example.com', 'department_id' => $deptPrd->id]);
        }
        if ($deptQc) {
            Pic::firstOrCreate(['nik' => 'PIC003'], ['name' => 'Siti Nurhaliza', 'phone' => '08123456781', 'email' => 'siti@example.com', 'department_id' => $deptQc->id]);
        }

        // 4. Sample Items
        $catCB = Category::where('name', 'CARTON BOX')->first();
        $catLY = Category::where('name', 'LAYER')->first();
        $catAK = Category::where('name', 'ALAT KEBERSIHAN')->first();

        if ($catCB) {
            Item::firstOrCreate(
                ['code' => 'CB-001'],
                [
                    'name' => 'Karton Box Ukuran Standard A',
                    'description' => 'Karton box tebal 5mm untuk packaging',
                    'category_id' => $catCB->id,
                    'location_id' => $locMain->id,
                    'warehouse_id' => $whMain->id,
                    'unit' => 'PCS',
                    'min_stock' => 50,
                    'current_stock' => 500,
                    'price' => 15000.00,
                ]
            );
            Item::firstOrCreate(
                ['code' => 'CB-002'],
                [
                    'name' => 'Karton Box Ukuran Small B',
                    'description' => 'Karton box tebal 3mm',
                    'category_id' => $catCB->id,
                    'location_id' => $locMain->id,
                    'warehouse_id' => $whMain->id,
                    'unit' => 'PCS',
                    'min_stock' => 20,
                    'current_stock' => 200,
                    'price' => 8500.00,
                ]
            );
        }

        if ($catLY) {
            Item::firstOrCreate(
                ['code' => 'LY-001'],
                [
                    'name' => 'Layer Corrugated Board 100x120',
                    'description' => 'Layer pembatas antar barang',
                    'category_id' => $catLY->id,
                    'location_id' => $locSub->id,
                    'warehouse_id' => $whSub->id,
                    'unit' => 'LEMBAR',
                    'min_stock' => 100,
                    'current_stock' => 1000,
                    'price' => 5000.00,
                ]
            );
        }

        if ($catAK) {
            Item::firstOrCreate(
                ['code' => 'AK-001'],
                [
                    'name' => 'Sapu Ijuk Standar GA',
                    'description' => 'Sapu ijuk kebersihan area kerja',
                    'category_id' => $catAK->id,
                    'location_id' => $locMain->id,
                    'warehouse_id' => $whMain->id,
                    'unit' => 'UNIT',
                    'min_stock' => 5,
                    'current_stock' => 25,
                    'price' => 25000.00,
                ]
            );
        }
    }
}
