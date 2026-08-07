<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('items', 'min_stock')) {
            Schema::table('items', function (Blueprint $table) {
                $table->integer('min_stock')->default(0)->after('stock');
            });
        }

        if (!Schema::hasColumn('stock_outs', 'pic_id')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->foreignId('pic_id')->nullable()->constrained('pics')->onDelete('set null')->after('item_id');
            });
        }

        if (!Schema::hasColumn('stock_outs', 'department_id')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('items', 'min_stock')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('min_stock');
            });
        }

        if (Schema::hasColumn('stock_outs', 'pic_id')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->dropColumn('pic_id');
            });
        }

        if (Schema::hasColumn('stock_outs', 'department_id')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->dropColumn('department_id');
            });
        }
    }
};