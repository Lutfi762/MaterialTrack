<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->string('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_returns');
    }
};
