<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('qty')->comment('Bertanda: positif = tambah stok, negatif = kurangi stok');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'created_at']);
            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
