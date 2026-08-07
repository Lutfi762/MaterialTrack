<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DepartmentUserSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();

        if ($departments->isEmpty()) {
            $this->command->warn('Belum ada data departemen. Jalankan seeder departemen terlebih dahulu.');
            return;
        }

        foreach ($departments as $dept) {
            // Buat email dari code department, misal: PROD@materialtrack.local
            $emailSlug = Str::slug($dept->code ?: $dept->name, '');
            $email = strtolower($emailSlug) . '@materialtrack.local';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'          => $dept->name . ' - Department Account',
                    'password'      => Hash::make('password123'),
                    'department_id' => $dept->id,
                ]
            );

            // Pastikan department_id dan role selalu sinkron, walau user sudah ada
            $user->update(['department_id' => $dept->id]);
            $user->syncRoles(['department_user']);

            $this->command->info("User dibuat/disinkron: {$email} (Departemen: {$dept->name})");
        }

        $this->command->info('Selesai. Semua akun department pakai password default: password123');
    }
}