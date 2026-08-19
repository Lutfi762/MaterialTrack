<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('unit_alt')->nullable()->after('unit');
            $table->decimal('conversion_factor', 8, 2)->nullable()->after('unit_alt');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['unit_alt', 'conversion_factor']);
        });
    }
};
