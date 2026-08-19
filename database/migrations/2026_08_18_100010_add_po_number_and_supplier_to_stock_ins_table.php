<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('reference_no');
            $table->foreignId('supplier_id')->nullable()->after('source')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['po_number', 'supplier_id']);
        });
    }
};
