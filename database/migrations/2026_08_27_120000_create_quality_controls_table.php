<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->string('qc_number')->unique();
            $table->string('reference_number')->nullable();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->integer('qty_inspected');
            $table->integer('qty_passed')->default(0);
            $table->integer('qty_rejected')->default(0);
            $table->string('inspector_name')->nullable();
            $table->enum('status', ['passed', 'failed', 'partial'])->default('passed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('quality_controls');
    }
};