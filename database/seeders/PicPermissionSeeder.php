<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PicPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permission untuk PIC
        $picPermissions = ['pics.index', 'pics.create', 'pics.edit', 'pics.delete'];

        foreach ($picPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Permission untuk AI Assistant
        Permission::firstOrCreate(['name' => 'ai_assistant.use', 'guard_name' => 'web']);

        // Admin dapat akses penuh ke PIC + AI Assistant
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole?->givePermissionTo(array_merge($picPermissions, ['ai_assistant.use']));

        // Department user cuma boleh lihat & tambah PIC (tidak edit/hapus),
        // plus akses AI Assistant untuk analisis inventory departemennya sendiri.
        $departmentRole = Role::where('name', 'department_user')->first();
        $departmentRole?->givePermissionTo(['pics.index', 'pics.create', 'ai_assistant.use']);
    }
}