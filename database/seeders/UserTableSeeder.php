<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Dapatkan role admin & assign semua permissions ke role admin
        $roleAdmin = Role::whereName('admin')->first();
        $permissions = Permission::all();
        $roleAdmin?->syncPermissions($permissions);

        // 2. Create User Admin (Gudang/System)
        $admin = User::firstOrCreate(
            ['email' => 'admin@materialtrack.com'],
            [
                'name'          => 'Administrator Gudang',
                'password'      => bcrypt('password'),
                'department_id' => null,
            ]
        );
        if ($roleAdmin) {
            $admin->assignRole($roleAdmin);
        }

        // 3. Dapatkan role department_user
        $roleDeptUser = Role::whereName('department_user')->first();

        // 4. Create User Departemen IT
        $itDept = Department::whereCode('DEPT-IT')->first();
        $userIT = User::firstOrCreate(
            ['email' => 'it@materialtrack.com'],
            [
                'name'          => 'User IT Material',
                'password'      => bcrypt('password'),
                'department_id' => $itDept?->id,
            ]
        );
        if ($roleDeptUser) {
            $userIT->assignRole($roleDeptUser);
        }

        // 5. Create User Departemen Produksi
        $prdDept = Department::whereCode('DEPT-PRD')->first();
        $userPrd = User::firstOrCreate(
            ['email' => 'produksi@materialtrack.com'],
            [
                'name'          => 'User Produksi',
                'password'      => bcrypt('password'),
                'department_id' => $prdDept?->id,
            ]
        );
        if ($roleDeptUser) {
            $userPrd->assignRole($roleDeptUser);
        }
    }
}