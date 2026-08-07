<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'stock_before')) {
                $table->integer('stock_before')->nullable()->after('qty');
            }
            if (!Schema::hasColumn('stock_movements', 'notes')) {
                $table->text('notes')->nullable()->after('ref_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['stock_before', 'notes']);
        });
    }
};