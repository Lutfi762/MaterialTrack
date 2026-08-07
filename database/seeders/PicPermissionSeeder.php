<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PicPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['pics.index', 'pics.create', 'pics.edit', 'pics.delete'];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole?->givePermissionTo($permissions);
    }
}