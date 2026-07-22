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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke tabel categories (opsional/nullable)
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            
            // Relasi ke tabel locations (opsional/nullable)
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit');
            $table->integer('min_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};