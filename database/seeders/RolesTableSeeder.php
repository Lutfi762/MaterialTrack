<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Gunakan firstOrCreate agar tidak error jika role sudah ada
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'department_user', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
    }
}
