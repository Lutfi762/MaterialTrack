<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration ini raw-SQL MySQL-only (information_schema.REFERENTIAL_CONSTRAINTS).
        // ponytail: skip di driver lain (sqlite test env); upgrade jika perlu FK audit lintas-DB.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // ============ HRM ============
        $this->ensureFk('attendances', 'employee_id', 'employees', 'RESTRICT');
        $this->ensureUnique('attendances', ['employee_id', 'date'], 'attendances_employee_id_date_unique');

        $this->ensureFk('leaves', 'employee_id', 'employees', 'RESTRICT');
        $this->ensureFk('leaves', 'leave_type_id', 'leave_types', 'RESTRICT');
        if (Schema::hasTable('leaves') && ! Schema::hasColumn('leaves', 'rejection_reason')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('reason');
            });
        }

        $this->ensureFk('payroll_items', 'employee_id', 'employees', 'RESTRICT');
        $this->ensureFk('employee_contracts', 'employee_id', 'employees', 'RESTRICT');

        if (Schema::hasTable('payroll_periods')) {
            if (! Schema::hasColumn('payroll_periods', 'pay_date')) {
                Schema::table('payroll_periods', function (Blueprint $table) {
                    $table->date('pay_date')->nullable()->after('end_date');
                });
            }
            if (! Schema::hasColumn('payroll_periods', 'total_amount')) {
                Schema::table('payroll_periods', function (Blueprint $table) {
                    $table->decimal('total_amount', 15, 2)->default(0)->after('pay_date');
                });
            }
            if (! Schema::hasColumn('payroll_periods', 'notes')) {
                Schema::table('payroll_periods', function (Blueprint $table) {
                    $table->text('notes')->nullable()->after('total_amount');
                });
            }

            $enum = DB::selectOne("SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payroll_periods' AND COLUMN_NAME = 'status'");
            if ($enum && ! str_contains($enum->t, 'approved')) {
                Schema::table('payroll_periods', function (Blueprint $table) {
                    $table->enum('status', ['draft', 'approved', 'processing', 'paid', 'completed', 'closed'])->default('draft')->change();
                });
            }
        }

        // ============ Procurement ============
        $this->ensureFk('purchase_requests', 'requested_by', 'users', 'RESTRICT');
        $this->ensureFk('purchase_request_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('purchase_orders', 'supplier_id', 'suppliers', 'RESTRICT');
        $this->ensureFk('purchase_orders', 'created_by', 'users', 'RESTRICT');
        $this->ensureFk('purchase_order_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('goods_receipts', 'purchase_order_id', 'purchase_orders', 'RESTRICT');
        $this->ensureFk('goods_receipts', 'received_by', 'users', 'RESTRICT');
        $this->ensureFk('goods_receipt_items', 'purchase_order_item_id', 'purchase_order_items', 'RESTRICT');
        $this->ensureFk('goods_receipt_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('purchase_invoices', 'purchase_order_id', 'purchase_orders', 'RESTRICT');
        $this->ensureFk('purchase_invoices', 'supplier_id', 'suppliers', 'RESTRICT');
        $this->ensureFk('purchase_invoices', 'created_by', 'users', 'RESTRICT');
        $this->ensureUnique('purchase_invoices', ['purchase_order_id'], 'purchase_invoices_purchase_order_id_unique');
        $this->ensureFk('purchase_invoice_items', 'item_id', 'items', 'RESTRICT');

        // ============ Sales ============
        $this->ensureFk('quotations', 'customer_id', 'customers', 'RESTRICT');
        $this->ensureFk('quotations', 'created_by', 'users', 'RESTRICT');
        $this->ensureFk('quotation_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('sales_orders', 'customer_id', 'customers', 'RESTRICT');
        $this->ensureFk('sales_orders', 'created_by', 'users', 'RESTRICT');
        $this->ensureUnique('sales_orders', ['quotation_id'], 'sales_orders_quotation_id_unique');
        $this->ensureFk('sales_order_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('deliveries', 'sales_order_id', 'sales_orders', 'RESTRICT');
        $this->ensureFk('deliveries', 'customer_id', 'customers', 'RESTRICT');
        $this->ensureFk('deliveries', 'dispatched_by', 'users', 'RESTRICT');
        $this->ensureFk('delivery_items', 'sales_order_item_id', 'sales_order_items', 'RESTRICT');
        $this->ensureFk('delivery_items', 'item_id', 'items', 'RESTRICT');

        $this->ensureFk('sales_invoices', 'sales_order_id', 'sales_orders', 'RESTRICT');
        $this->ensureFk('sales_invoices', 'customer_id', 'customers', 'RESTRICT');
        $this->ensureFk('sales_invoices', 'created_by', 'users', 'RESTRICT');
        $this->ensureUnique('sales_invoices', ['sales_order_id'], 'sales_invoices_sales_order_id_unique');
        $this->ensureFk('sales_invoice_items', 'item_id', 'items', 'RESTRICT');

        // ============ Finance ============
        $this->ensureFk('journal_entries', 'created_by', 'users', 'RESTRICT');
        $this->ensureFk('journal_entry_lines', 'chart_of_account_id', 'chart_of_accounts', 'RESTRICT');

        // ============ Approval workflows ============
        $this->ensureFk('approval_requests', 'requested_by', 'users', 'RESTRICT');

        // ============ Stock history (dihapus user/item = riwayat hilang) ============
        $this->ensureFk('stock_opnames', 'item_id', 'items', 'RESTRICT');
        $this->ensureFk('stock_opnames', 'user_id', 'users', 'RESTRICT');
        $this->ensureFk('stock_reservations', 'item_id', 'items', 'RESTRICT');
        $this->ensureFk('stock_reservations', 'user_id', 'users', 'RESTRICT');
        $this->ensureFk('stock_returns', 'item_id', 'items', 'RESTRICT');
        $this->ensureFk('stock_adjustments', 'item_id', 'items', 'RESTRICT');
        $this->ensureFk('stock_adjustments', 'user_id', 'users', 'RESTRICT');
        $this->ensureFk('bulk_stock_operations', 'user_id', 'users', 'RESTRICT');
    }
public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ([
            ['attendances', 'employee_id'], ['leaves', 'employee_id'], ['leaves', 'leave_type_id'],
            ['payroll_items', 'employee_id'], ['employee_contracts', 'employee_id'],
            ['purchase_requests', 'requested_by'], ['purchase_request_items', 'item_id'],
            ['purchase_orders', 'supplier_id'], ['purchase_orders', 'created_by'], ['purchase_order_items', 'item_id'],
            ['goods_receipts', 'purchase_order_id'], ['goods_receipts', 'received_by'],
            ['goods_receipt_items', 'purchase_order_item_id'], ['goods_receipt_items', 'item_id'],
            ['purchase_invoices', 'purchase_order_id'], ['purchase_invoices', 'supplier_id'], ['purchase_invoices', 'created_by'],
            ['purchase_invoice_items', 'item_id'],
            ['quotations', 'customer_id'], ['quotations', 'created_by'], ['quotation_items', 'item_id'],
            ['sales_orders', 'customer_id'], ['sales_orders', 'created_by'], ['sales_order_items', 'item_id'],
            ['deliveries', 'sales_order_id'], ['deliveries', 'customer_id'], ['deliveries', 'dispatched_by'],
            ['delivery_items', 'sales_order_item_id'], ['delivery_items', 'item_id'],
            ['sales_invoices', 'sales_order_id'], ['sales_invoices', 'customer_id'], ['sales_invoices', 'created_by'],
            ['sales_invoice_items', 'item_id'],
            ['journal_entries', 'created_by'], ['journal_entry_lines', 'chart_of_account_id'],
            ['approval_requests', 'requested_by'],
            ['stock_opnames', 'item_id'], ['stock_opnames', 'user_id'],
            ['stock_reservations', 'item_id'], ['stock_reservations', 'user_id'],
            ['stock_returns', 'item_id'],
            ['stock_adjustments', 'item_id'], ['stock_adjustments', 'user_id'],
            ['bulk_stock_operations', 'user_id'],
        ] as [$table, $column]) {
            $info = $this->fkInfo($table, $column);
            if ($info) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$info->cn}`");
            }
        }

        if (Schema::hasTable('attendances') && Schema::hasIndex('attendances', 'attendances_employee_id_date_unique')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique('attendances_employee_id_date_unique');
            });
        }
        if (Schema::hasTable('purchase_invoices') && Schema::hasIndex('purchase_invoices', 'purchase_invoices_purchase_order_id_unique')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                $table->dropUnique('purchase_invoices_purchase_order_id_unique');
            });
        }
        if (Schema::hasTable('sales_orders') && Schema::hasIndex('sales_orders', 'sales_orders_quotation_id_unique')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropUnique('sales_orders_quotation_id_unique');
            });
        }
        if (Schema::hasTable('sales_invoices') && Schema::hasIndex('sales_invoices', 'sales_invoices_sales_order_id_unique')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropUnique('sales_invoices_sales_order_id_unique');
            });
        }

        foreach (['leaves.rejection_reason', 'payroll_periods.pay_date', 'payroll_periods.total_amount', 'payroll_periods.notes'] as $c) {
            [$t, $col] = explode('.', $c);
            if (Schema::hasTable($t) && Schema::hasColumn($t, $col)) {
                Schema::table($t, function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }

    private function fkInfo(string $table, string $column): ?object
    {
        return DB::selectOne("SELECT rc.CONSTRAINT_NAME AS cn, rc.DELETE_RULE AS dr
            FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            JOIN information_schema.KEY_COLUMN_USAGE kcu
                ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.TABLE_NAME = rc.TABLE_NAME
            WHERE rc.CONSTRAINT_SCHEMA = DATABASE() AND rc.TABLE_NAME = ? AND kcu.COLUMN_NAME = ?", [$table, $column]);
    }

    private function ensureFk(string $table, string $column, string $refTable, string $onDelete): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }
        $current = $this->fkInfo($table, $column);
        if ($current && strtoupper($current->dr) === strtoupper($onDelete)) {
            return;
        }
        if ($current) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$current->cn}`");
        }
        DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$column}_foreign`
            FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`id`) ON DELETE {$onDelete}");
    }

    private function ensureUnique(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                $blueprint->unique($columns, $name);
            });
        } catch (\Throwable) {
            // data lama duplikat — biarkan tanpa unique
        }
    }
};