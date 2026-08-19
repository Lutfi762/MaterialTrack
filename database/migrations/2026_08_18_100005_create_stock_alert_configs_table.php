<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alert_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('alert_type'); // low_stock, expiry, min_stock
            $table->boolean('email_enabled')->default(true);
            $table->boolean('browser_enabled')->default(true);
            $table->integer('threshold_days')->default(30); // for expiry alerts
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alert_configs');
    }
};
