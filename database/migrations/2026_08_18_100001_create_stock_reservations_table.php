<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->string('purpose')->nullable();
            $table->string('project_name')->nullable();
            $table->string('status')->default('active'); // active, fulfilled, cancelled
            $table->date('reserved_until')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
