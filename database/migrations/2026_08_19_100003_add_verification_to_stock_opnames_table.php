<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('notes');
            $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        // Data opname lama sudah diterapkan langsung saat itu juga,
        // jadi ditandai sebagai terverifikasi.
        DB::table('stock_opnames')->update([
            'status' => 'verified',
            'verified_by' => DB::raw('user_id'),
            'verified_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn(['status', 'verified_by', 'verified_at']);
        });
    }
};
