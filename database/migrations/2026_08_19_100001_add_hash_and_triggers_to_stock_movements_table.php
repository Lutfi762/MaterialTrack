<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('hash', 64)->nullable()->after('notes')->index();
        });

        // Backfill hash chain untuk data lama (urutan by id)
        $prevHash = null;

        DB::table('stock_movements')->orderBy('id')->chunkById(500, function ($rows) use (&$prevHash) {
            foreach ($rows as $row) {
                $prevHash = hash('sha256', ($prevHash ?: 'GENESIS').'|'.implode('|', [
                    $row->item_id ?? '',
                    $row->user_id ?? '',
                    $row->pic_id ?? '',
                    $row->type ?? '',
                    $row->qty ?? '',
                    $row->stock_before ?? '',
                    $row->balance_after ?? '',
                    $row->ref_type ?? '',
                    $row->ref_id ?? '',
                    $row->notes ?? '',
                    $row->created_at ?? '',
                ]));

                DB::table('stock_movements')->where('id', $row->id)->update(['hash' => $prevHash]);
            }
        });

        // Ledger bersifat append-only: UPDATE/DELETE langsung ditolak di level database.
        // (Trigger MySQL; pada driver lain cukup dienkripsi via model booted()/hash)
        if (DB::getDriverName() === 'mysql') {
            DB::statement(<<<'SQL'
                CREATE TRIGGER stock_movements_deny_update
                BEFORE UPDATE ON stock_movements
                FOR EACH ROW
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'stock_movements is an append-only audit ledger; updates are forbidden'
            SQL);

            DB::statement(<<<'SQL'
                CREATE TRIGGER stock_movements_deny_delete
                BEFORE DELETE ON stock_movements
                FOR EACH ROW
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'stock_movements is an append-only audit ledger; deletes are forbidden'
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS stock_movements_deny_update');
            DB::statement('DROP TRIGGER IF EXISTS stock_movements_deny_delete');
        }

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->dropColumn('hash');
        });
    }
};
