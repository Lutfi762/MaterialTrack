<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'DEPT-IT', 'name' => 'IT & Infrastructure'],
            ['code' => 'DEPT-HR', 'name' => 'Human Resources'],
            ['code' => 'DEPT-PRD', 'name' => 'Production / Workshop'],
            ['code' => 'DEPT-GA', 'name' => 'General Affairs'],
             ['code' => 'DEPT-QC', 'name' => 'Quality Control'],
        ];
foreach ($departments as $dept) {
            // firstOrCreate: Hanya menambah jika data 'code' belum ada di database
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}