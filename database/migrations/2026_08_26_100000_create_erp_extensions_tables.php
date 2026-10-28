<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("fixed_assets", function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string("name");
            $table->text("description")->nullable();
            $table->enum("category", ["building", "vehicle", "machinery", "equipment", "furniture", "other"]);
            $table->date("acquisition_date");
            $table->decimal("acquisition_cost", 15, 2);
            $table->integer("useful_life_months");
            $table->decimal("salvage_value", 15, 2)->default(0);
            $table->enum("depreciation_method", ["straight_line", "declining_balance"])->default("straight_line");
            $table->decimal("accumulated_depreciation", 15, 2)->default(0);
            $table->decimal("current_book_value", 15, 2)->default(0);
            $table->enum("status", ["active", "disposed", "fully_depreciated"])->default("active");
            $table->foreignId("department_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("asset_account_id")->nullable()->references("id")->on("chart_of_accounts")->nullOnDelete();
            $table->foreignId("depreciation_account_id")->nullable()->references("id")->on("chart_of_accounts")->nullOnDelete();
            $table->foreignId("accumulated_dep_account_id")->nullable()->references("id")->on("chart_of_accounts")->nullOnDelete();
            $table->timestamp("disposed_at")->nullable();
            $table->text("notes")->nullable();
            $table->timestamps();
        });

        Schema::create("budgets", function (Blueprint $table) {
            $table->id();
            $table->foreignId("fiscal_year_id")->constrained()->cascadeOnDelete();
            $table->foreignId("department_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("chart_of_account_id")->constrained()->cascadeOnDelete();
            $table->decimal("budget_amount", 15, 2);
            $table->decimal("realized_amount", 15, 2)->default(0);
            $table->text("notes")->nullable();
            $table->timestamps();
            
            $table->unique(["fiscal_year_id", "department_id", "chart_of_account_id"], "budgets_unique_idx");
        });

        Schema::create("supplier_scorecards", function (Blueprint $table) {
            $table->id();
            $table->foreignId("supplier_id")->constrained()->cascadeOnDelete();
            $table->string("period", 7);
            $table->integer("total_po")->default(0);
            $table->decimal("total_po_value", 15, 2)->default(0);
            $table->integer("on_time_deliveries")->default(0);
            $table->integer("late_deliveries")->default(0);
            $table->integer("total_qty_received")->default(0);
            $table->integer("total_qty_rejected")->default(0);
            $table->decimal("delivery_score", 5, 2)->default(0);
            $table->decimal("quality_score", 5, 2)->default(0);
            $table->decimal("overall_score", 5, 2)->default(0);
            $table->timestamps();

            $table->unique(["supplier_id", "period"]);
        });

        Schema::table("items", function (Blueprint $table) {
            if (!Schema::hasColumn("items", "reorder_point")) {
                $table->integer("reorder_point")->default(0)->after("min_stock");
            }
            if (!Schema::hasColumn("items", "reorder_qty")) {
                $table->integer("reorder_qty")->default(0)->after("reorder_point");
            }
        });
    }

    public function down(): void
    {
        Schema::table("items", function (Blueprint $table) {
            $table->dropColumn(["reorder_point", "reorder_qty"]);
        });
        Schema::dropIfExists("supplier_scorecards");
        Schema::dropIfExists("budgets");
        Schema::dropIfExists("fixed_assets");
    }
};
