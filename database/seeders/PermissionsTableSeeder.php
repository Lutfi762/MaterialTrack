<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // 1. Dashboard
            'dashboard.index', 'dashboard.total_items', 'dashboard.min_stock_alert',
            // 2. Master Data - Users
            'users.index', 'users.create', 'users.edit', 'users.delete',
            // 3. Master Data - Roles & Permissions
            'roles.index', 'roles.create', 'roles.edit', 'roles.delete', 'permissions.index',
            // 4. Master Data - Categories
            'categories.index', 'categories.create', 'categories.edit', 'categories.delete',
            // 5. Master Data - Locations
            'locations.index', 'locations.create', 'locations.edit', 'locations.delete',
            // 6. Master Data - Departments
            'departments.index', 'departments.create', 'departments.edit', 'departments.delete',
            // 7. Master Data - PICs
            'pics.index', 'pics.create', 'pics.edit', 'pics.delete',
            // 8. Master Data - Items
            'items.index', 'items.create', 'items.edit', 'items.delete',
            // 9. Transaksi - Stock In
            'stock_ins.index', 'stock_ins.create', 'stock_ins.edit', 'stock_ins.delete', 'stock_ins.export',
            // 10. Transaksi - Stock Out
            'stock_outs.index', 'stock_outs.create', 'stock_outs.edit', 'stock_outs.delete', 'stock_outs.export', 'stock_outs.approve',
            // 11. Stock Movements (Read-only audit log)
            'stock_movements.index', 'stock_movements.export',
            // 12. Stock Opnames
            'stock_opnames.index', 'stock_opnames.create', 'stock_opnames.export', 'stock_opnames.verify',
            // 13. Reports
            'reports.index',
            // 14. AI Assistant
            'ai_assistant.use',
            // 15. Stock Transfers
            'stock_transfers.index', 'stock_transfers.create',
            // 16. Stock Adjustments (koreksi stok ber-approval)
            'stock_adjustments.index', 'stock_adjustments.create', 'stock_adjustments.approve',
            // 17. Audit Trail
            'activity_logs.index', 'activity_logs.export',

            // 18. ERP Extensions
            'company_profile.index', 'company_profile.edit',
            'customers.index', 'customers.create', 'customers.edit', 'customers.delete',
            'purchase_requests.index', 'purchase_requests.create', 'purchase_requests.edit', 'purchase_requests.delete', 'purchase_requests.approve',
            'purchase_orders.index', 'purchase_orders.create', 'purchase_orders.edit', 'purchase_orders.delete', 'purchase_orders.approve',
            'goods_receipts.index', 'goods_receipts.create', 'goods_receipts.edit', 'goods_receipts.delete',
            'purchase_invoices.index', 'purchase_invoices.create', 'purchase_invoices.edit', 'purchase_invoices.delete',
            'quotations.index', 'quotations.create', 'quotations.edit', 'quotations.delete',
            'sales_orders.index', 'sales_orders.create', 'sales_orders.edit', 'sales_orders.delete', 'sales_orders.approve',
            'deliveries.index', 'deliveries.create', 'deliveries.edit', 'deliveries.delete',
            'sales_invoices.index', 'sales_invoices.create', 'sales_invoices.edit', 'sales_invoices.delete',
            'chart_of_accounts.index', 'chart_of_accounts.create', 'chart_of_accounts.edit', 'chart_of_accounts.delete',
            'journal_entries.index', 'journal_entries.create',
            'financial_reports.index',
            'cash_bank.index', 'cash_bank.create',
            'sales_returns.index', 'sales_returns.create', 'sales_returns.edit', 'sales_returns.delete', 'sales_returns.approve',
            'notifications.index',
            'employees.index', 'employees.create', 'employees.edit', 'employees.delete',
            'attendances.index', 'attendances.create',
            'leaves.index', 'leaves.create', 'leaves.approve',
            'payroll.index', 'payroll.create', 'payroll.approve',
            'approval_workflows.index', 'approval_workflows.create',
            'stock_alerts.index', 'stock_alerts.edit',

            // 19. Master Data - Suppliers, Warehouses, Stock Reservations
            'suppliers.index', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'warehouses.index', 'warehouses.create', 'warehouses.edit', 'warehouses.delete',
                        'stock_reservations.index', 'stock_reservations.create',

                        'boms.index', 'boms.create', 'boms.delete',
            'production_orders.index', 'production_orders.create', 'production_orders.complete',
            
                        'asset_maintenances.index', 'asset_maintenances.create', 'asset_maintenances.edit', 'asset_maintenances.delete',
                        'quality_controls.index', 'quality_controls.create', 'quality_controls.delete',
                        'projects.index', 'projects.create', 'projects.delete',
                        'crm.index',
                        'fleet.index',
            'helpdesk.index', 'helpdesk.create', 'helpdesk.update',
            'documents.index', 'documents.create', 'documents.delete',
            // 20. Fixed Assets
            'fixed_assets.index', 'fixed_assets.create', 'fixed_assets.edit', 'fixed_assets.delete',
            // 21. Budgets
            'budgets.index', 'budgets.create', 'budgets.edit', 'budgets.delete',
            // 22. Supplier Scorecards
            'supplier_scorecards.index', 'supplier_scorecards.generate',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign ke Role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $deptRole = Role::firstOrCreate(['name' => 'department_user', 'guard_name' => 'web']);
        $deptRole->syncPermissions([
            'dashboard.index',
            'items.index',
            'stock_outs.index',
            'stock_outs.create',
            'stock_reservations.index',
            'stock_reservations.create',
            'ai_assistant.use',
        ]);

        // Role auditor: akses baca penuh untuk verifikasi independen
        $auditorRole = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
        $auditorRole->syncPermissions([
            'dashboard.index', 'dashboard.total_items', 'dashboard.min_stock_alert',
            'items.index', 'categories.index', 'departments.index', 'locations.index', 'pics.index',
            'users.index', 'roles.index', 'permissions.index',
            'stock_ins.index', 'stock_ins.export',
            'stock_outs.index', 'stock_outs.export',
            'stock_movements.index', 'stock_movements.export',
            'stock_opnames.index', 'stock_opnames.export',
            'stock_transfers.index',
            'stock_adjustments.index',
            'reports.index',
            'activity_logs.index', 'activity_logs.export',
            'financial_reports.index',
            'cash_bank.index',
            'sales_returns.index',
            'notifications.index',
        ]);
    }
}








