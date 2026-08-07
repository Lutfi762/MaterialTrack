<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('period', 7); // format: 'YYYY-MM'
            $table->date('date');
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference'); // physical - system
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'period']); // 1 item hanya 1x opname per bulan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};