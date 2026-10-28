<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bug: DeliveryController membuat StockOut untuk pengiriman ke customer
 * (tidak punya konteks departemen internal) tanpa mengisi department_id,
 * tapi kolom ini NOT NULL sejak migration awal -> INSERT gagal (500).
 * Konsisten dengan stock_ins.department_id yang sudah nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
        });
    }
};
