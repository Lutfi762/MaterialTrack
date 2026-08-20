<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Penyembuh skema legacy: kolom yang direferensikan migration lama
 * (2026_08_18_100010 / 2026_08_19_100001 / 2026_08_19_100003) dicatat
 * "Ran" tapi DDL-nya TIDAK pernah terpasang di DB (file diedit setelah
 * running). Guarded supaya aman difresh/setengah-terpasang.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. departments.status — dipakai DepartmentController (active/inactive)
        if (! Schema::hasColumn('departments', 'status')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->after('description');
            });
        }

        // 2. stock_ins.po_number + supplier_id — dipakai StockInController & relasi supplier()
        if (! Schema::hasColumn('stock_ins', 'po_number')) {
            Schema::table('stock_ins', function (Blueprint $table) {
                $table->string('po_number')->nullable()->after('reference_no');
            });
        }
        if (! Schema::hasColumn('stock_ins', 'supplier_id')) {
            Schema::table('stock_ins', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('source')->constrained()->nullOnDelete();
            });
        }

        // 3. stock_movements.hash — rantai ledger dipakai model booted()
        if (! Schema::hasColumn('stock_movements', 'hash')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('hash', 64)->nullable()->after('notes')->index();
            });

            // Backfill rantai hash (urutan by id) — SEBELUM trigger dibuat,
            // karena trigger deny-update akan menolak update backfill ini.
            $prevHash = null;
            DB::table('stock_movements')->orderBy('id')->chunkById(500, function ($rows) use (&$prevHash) {
                foreach ($rows as $row) {
                    $prevHash = hash('sha256', ($prevHash ?: 'GENESIS').'|'.implode('|', [
                        $row->item_id ?? '', $row->user_id ?? '', $row->pic_id ?? '',
                        $row->type ?? '', $row->qty ?? '', $row->stock_before ?? '',
                        $row->balance_after ?? '', $row->ref_type ?? '', $row->ref_id ?? '',
                        $row->notes ?? '', $row->created_at ?? '',
                    ]));
                    DB::table('stock_movements')->where('id', $row->id)->update(['hash' => $prevHash]);
                }
            });

            // Ledger append-only: UPDATE/DELETE ditolak di level DB.
            if (DB::getDriverName() === 'mysql') {
                $triggers = array_column(
                    DB::select("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND EVENT_OBJECT_TABLE = 'stock_movements'"),
                    'TRIGGER_NAME'
                );

                if (! in_array('stock_movements_deny_update', $triggers, true)) {
                    DB::statement(<<<'SQL'
                        CREATE TRIGGER stock_movements_deny_update
                        BEFORE UPDATE ON stock_movements
                        FOR EACH ROW
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'stock_movements is an append-only audit ledger; updates are forbidden'
                    SQL);
                }

                if (! in_array('stock_movements_deny_delete', $triggers, true)) {
                    DB::statement(<<<'SQL'
                        CREATE TRIGGER stock_movements_deny_delete
                        BEFORE DELETE ON stock_movements
                        FOR EACH ROW
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'stock_movements is an append-only audit ledger; deletes are forbidden'
                    SQL);
                }
            }
        }

        // 4. stock_opnames.status / verified_by / verified_at — dipakai StockOpnameController store/verify
        if (! Schema::hasColumn('stock_opnames', 'status')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')->after('notes');
            });
        }
        if (! Schema::hasColumn('stock_opnames', 'verified_by')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            });
        }
        if (! Schema::hasColumn('stock_opnames', 'verified_at')) {
            Schema::table('stock_opnames', function (Blueprint $table) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            });
        }

        // Baris lama (sebelum kolom status ada) tergolong sudah diterapkan
        // langsung saat itu juga -> tandai terverifikasi.
        DB::table('stock_opnames')->whereNull('status')->update([
            'status' => 'verified',
            'verified_by' => DB::raw('user_id'),
            'verified_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS stock_movements_deny_update');
            DB::statement('DROP TRIGGER IF EXISTS stock_movements_deny_delete');
        }

        Schema::table('stock_ins', function (Blueprint $table) {
            if (Schema::hasColumn('stock_ins', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
            if (Schema::hasColumn('stock_ins', 'po_number')) {
                $table->dropColumn('po_number');
            }
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('stock_movements', 'hash')) {
                $table->dropIndex(['hash']);
                $table->dropColumn('hash');
            }
        });
        Schema::table('stock_opnames', function (Blueprint $table) {
            if (Schema::hasColumn('stock_opnames', 'status')) {
                $table->dropColumn(['status', 'verified_by', 'verified_at']);
            }
        });
        if (Schema::hasColumn('departments', 'status')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};