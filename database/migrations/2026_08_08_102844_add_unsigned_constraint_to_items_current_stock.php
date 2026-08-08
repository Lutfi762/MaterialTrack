<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Change current_stock to unsigned to prevent negative values
            $table->unsignedInteger('current_stock')->default(0)->change();
            $table->unsignedInteger('min_stock')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Revert back to signed integer
            $table->integer('current_stock')->default(0)->change();
            $table->integer('min_stock')->default(0)->change();
        });
    }
};
