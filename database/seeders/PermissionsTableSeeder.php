<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
            // 7. Master Data - Items
            'items.index', 'items.create', 'items.edit', 'items.delete',
            // 8. Transaksi - Stock In
            'stock_ins.index', 'stock_ins.create', 'stock_ins.delete',
            // 9. Transaksi - Stock Out
            'stock_outs.index', 'stock_outs.create', 'stock_outs.approve', 'stock_outs.delete',
            // 10. Reports
            'reports.index',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign ke Role
        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions(Permission::all());

        $deptRole = Role::findByName('department_user');
        $deptRole->syncPermissions([
            'dashboard.index',
            'items.index',
            'stock_outs.index',
            'stock_outs.create',
        ]);
    }
}