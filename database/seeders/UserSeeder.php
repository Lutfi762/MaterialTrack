<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Super Admin
        $admin = User::create([
            'name'          => 'Administrator Gudang',
            'email'         => 'admin@materialtrack.com',
            'password'      => Hash::make('password'),
            'department_id' => null,
        ]);
        $admin->assignRole('admin');

        // 2. Buat Akun User Departemen IT
        $itDept = Department::query()->where('code', 'DEPT-IT')->first();
        $userIT = User::create([
            'name'          => 'User IT Material',
            'email'         => 'it@materialtrack.com',
            'password'      => Hash::make('password'),
            'department_id' => $itDept?->id,
        ]);
        $userIT->assignRole('department_user');

        // 3. Buat Akun User Departemen Production
        $prdDept = Department::query()->where('code', 'DEPT-PRD')->first();
        $userPrd = User::create([
            'name'          => 'User Produksi',
            'email'         => 'produksi@materialtrack.com',
            'password'      => Hash::make('password'),
            'department_id' => $prdDept?->id,
        ]);
        $userPrd->assignRole('department_user');
    }
}