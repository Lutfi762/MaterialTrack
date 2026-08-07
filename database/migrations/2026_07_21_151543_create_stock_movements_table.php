<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->enum('type', ['in', 'out']);
            $table->integer('qty');
            $table->integer('balance_after');
            $table->string('ref_type');
            $table->unsignedBigInteger('ref_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};