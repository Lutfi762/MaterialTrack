<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indeks performa untuk query filter/agregat yang sering dipakai
     * (dashboard, ledger item, laporan keuangan, log aktivitas).
     * Guarded: lewati bila tabel/kolom/index sudah ada.
     */
    public function up(): void
    {
        $this->addIndex('stock_movements', ['item_id', 'type', 'created_at'], 'stock_movements_item_id_type_created_at_index');
        $this->addIndex('stock_ins', ['supplier_id', 'date'], 'stock_ins_supplier_id_date_index');
        $this->addIndex('activity_logs', ['user_id', 'created_at'], 'activity_logs_user_id_created_at_index');
        $this->addIndex('journal_entries', ['status', 'entry_date'], 'journal_entries_status_entry_date_index');
        $this->addIndex('journal_entry_lines', ['chart_of_account_id'], 'journal_entry_lines_chart_of_account_id_index');
        $this->addIndex('cash_bank_transactions', ['transaction_date'], 'cash_bank_transactions_transaction_date_index');
        $this->addIndex('cash_bank_transactions', ['type', 'transaction_date'], 'cash_bank_transactions_type_transaction_date_index');
    }

    private function addIndex(string $table, array $cols, string $name): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $cols[0])) {
            return;
        }

        try {
            $exists = collect(DB::select("SHOW INDEX FROM `{$table}`"))
                ->contains(fn ($i) => $i->Key_name === $name);
        } catch (\Throwable) {
            return;
        }

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($cols, $name) {
            $t->index($cols, $name);
        });
    }

    public function down(): void
    {
        $this->dropIndex('stock_movements', 'stock_movements_item_id_type_created_at_index');
        $this->dropIndex('stock_ins', 'stock_ins_supplier_id_date_index');
        $this->dropIndex('activity_logs', 'activity_logs_user_id_created_at_index');
        $this->dropIndex('journal_entries', 'journal_entries_status_entry_date_index');
        $this->dropIndex('journal_entry_lines', 'journal_entry_lines_chart_of_account_id_index');
        $this->dropIndex('cash_bank_transactions', 'cash_bank_transactions_transaction_date_index');
        $this->dropIndex('cash_bank_transactions', 'cash_bank_transactions_type_transaction_date_index');
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($name) {
                $t->dropIndex($name);
            });
        } catch (\Throwable) {
            // index tidak ada — abaikan
        }
    }
};
