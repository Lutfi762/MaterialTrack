<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_stock_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // in, out
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bulk_stock_operation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_operation_id')->constrained('bulk_stock_operations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_stock_operation_items');
        Schema::dropIfExists('bulk_stock_operations');
    }
};
