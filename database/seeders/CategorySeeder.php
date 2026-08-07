<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $qc = Department::where('code', 'DEPT-QC')->first();
        $ga = Department::where('code', 'DEPT-GA')->first();

        $categories = [
            ['name' => 'CARTON BOX', 'code_prefix' => 'CB', 'department_ids' => array_filter([$qc?->id])],
            ['name' => 'LAYER', 'code_prefix' => 'LY', 'department_ids' => array_filter([$qc?->id])],
            ['name' => 'PARTITION', 'code_prefix' => 'PT', 'department_ids' => array_filter([$qc?->id])],
            ['name' => 'ALAT KEBERSIHAN', 'code_prefix' => 'AK', 'department_ids' => array_filter([$ga?->id])],
        ];

        foreach ($categories as $data) {
            $category = Category::firstOrCreate(
                ['name' => $data['name']],
                ['code_prefix' => $data['code_prefix']]
            );

            // Pastikan code_prefix ikut ter-update kalau kategori sudah ada
            // tapi code_prefix-nya masih kosong (misal dari input manual lama).
            if (empty($category->code_prefix)) {
                $category->update(['code_prefix' => $data['code_prefix']]);
            }

            if (!empty($data['department_ids'])) {
                $category->departments()->syncWithoutDetaching($data['department_ids']);
            }
        }
    }
}