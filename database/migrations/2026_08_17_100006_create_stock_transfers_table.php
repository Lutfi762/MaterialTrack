<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('item_id')->constrained();
            $table->foreignId('from_department_id')->constrained('departments');
            $table->foreignId('to_department_id')->constrained('departments');
            $table->integer('qty');
            $table->string('unit')->default('pcs');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
