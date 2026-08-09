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
        // Add indexes to stock_ins table for common queries
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->index('date'); // Filter by date range
            $table->index('created_at'); // Dashboard queries (today's transactions)
        });

        // Add indexes to stock_outs table for common queries
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->index('date'); // Filter by date range
            $table->index('created_at'); // Dashboard queries (today's transactions, 7-day chart)
            $table->index(['item_id', 'created_at']); // Top products query optimization
        });

        // Add indexes to stock_movements table for filter and audit queries
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('type'); // Filter by movement type (in/out/opname/adjustment)
            $table->index('created_at'); // Date range filters
            $table->index(['ref_type', 'ref_id']); // Polymorphic relation lookups
        });

        // Add index to stock_opnames for period-based queries
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->index('period'); // Monthly opname queries
            $table->index('date'); // Date range filters
        });

        // Add indexes to items for status calculations
        Schema::table('items', function (Blueprint $table) {
            $table->index('current_stock'); // Stock status queries
            $table->index(['current_stock', 'min_stock']); // Low stock alerts on dashboard
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['item_id', 'created_at']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['ref_type', 'ref_id']);
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropIndex(['period']);
            $table->dropIndex(['date']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['current_stock']);
            $table->dropIndex(['current_stock', 'min_stock']);
        });
    }
};
