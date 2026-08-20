<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'employee_code')) {
                    $table->string('employee_code', 255)->nullable()->after('employee_id');
                }
                if (!Schema::hasColumn('employees', 'designation')) {
                    $table->string('designation', 255)->nullable()->after('position');
                }
                if (!Schema::hasColumn('employees', 'join_date')) {
                    $table->date('join_date')->nullable()->after('hire_date');
                }
                if (!Schema::hasColumn('employees', 'status')) {
                    $table->string('status', 20)->default('active')->after('employment_status');
                }
            });

            // backfill dari kolom lama
            DB::table('employees')->whereNull('employee_code')->update([
                'employee_code' => DB::raw('employee_id'),
            ]);
            DB::table('employees')->whereNull('designation')->update([
                'designation' => DB::raw('position'),
            ]);
            DB::table('employees')->whereNull('join_date')->update([
                'join_date' => DB::raw('hire_date'),
            ]);

            // UI pakai 'internship' (form), enum lama pakai 'intern'
            DB::statement("ALTER TABLE `employees` MODIFY `employment_status` ENUM('permanent','contract','probation','internship','intern','resigned') NOT NULL DEFAULT 'permanent'");

            try {
                if (!Schema::hasIndex('employees', 'employees_employee_code_unique')) {
                    Schema::table('employees', function (Blueprint $table) {
                        $table->unique('employee_code');
                    });
                }
            } catch (\Throwable $e) {
                // data lama duplikat → lewati unique index
            }
        }

        // LeaveController + Leaves/Create.vue memakai 'days_allowed'
        if (Schema::hasTable('leave_types') && !Schema::hasColumn('leave_types', 'days_allowed')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->integer('days_allowed')->nullable()->after('default_days');
            });
            DB::table('leave_types')->whereNull('days_allowed')->update([
                'days_allowed' => DB::raw('default_days'),
            ]);
        }

        // Attendances/Create.vue memakai 'on_leave'
        if (Schema::hasTable('attendances')) {
            DB::statement("ALTER TABLE `attendances` MODIFY `status` ENUM('present','late','absent','sick','leave','holiday','on_leave') NOT NULL DEFAULT 'present'");
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasIndex('employees', 'employees_employee_code_unique')) {
                $table->dropUnique(['employee_code']);
            }
            foreach (['employee_code', 'designation', 'join_date', 'status'] as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        if (Schema::hasTable('leave_types') && Schema::hasColumn('leave_types', 'days_allowed')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropColumn('days_allowed');
            });
        }
    }
};