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
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('reference_no')->unique();
            $table->integer('qty');
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();

            // relationship items
            $table->foreign('item_id')->references('id')->on('items');

            // relationship departments
            $table->foreign('department_id')->references('id')->on('departments');

            // relationship users
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};