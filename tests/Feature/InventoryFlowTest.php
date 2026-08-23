<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Uji terhadap MySQL dev; semua data test di-rollback otomatis
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'materialtrack']);
        DB::connection('mysql')->beginTransaction();

        // Skip SEMUA middleware (CSRF, Inertia, SecurityHeaders) untuk test HTTP
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();
        config(['database.default' => 'sqlite']);
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();
    }

    private function makeItem(int $stock = 0): Item
    {
        return Item::create([
            'category_id' => null,
            'code' => 'TEST-'.uniqid(),
            'name' => 'Test Item '.uniqid(),
            'unit' => 'pcs',
            'min_stock' => 1,
            'current_stock' => $stock,
        ]);
    }

    public function test_stock_in_menambah_stok_dan_mencatat_ledger(): void
    {
        $this->actingAs($this->admin());

        $item = $this->makeItem(10);

        $this->post('/apps/stock-ins', [
            'item_id' => $item->id,
            'qty' => 5,
            'date' => now()->toDateString(),
            'source' => 'TEST',
        ])->assertRedirect();

        $this->assertSame(15, $item->refresh()->current_stock);

        $this->assertDatabaseHas('stock_movements', [
            'item_id' => $item->id,
            'type' => 'in',
            'qty' => 5,
            'stock_before' => 10,
            'balance_after' => 15,
            'ref_type' => StockIn::class,
        ]);
    }

    public function test_gr_anti_over_receive_dan_status_po_otomatis(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $item = $this->makeItem(0);
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'code' => 'SUP-'.uniqid(),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-TEST-'.uniqid(),
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'order_date' => now()->toDateString(),
            'status' => 'approved',
        ]);

        $poItem = $po->items()->create([
            'item_id' => $item->id,
            'qty' => 10,
            'unit' => 'pcs',
            'unit_price' => 100,
            'subtotal' => 1000,
        ]);

        $base = [
            'purchase_order_id' => $po->id,
            'receipt_date' => now()->toDateString(),
        ];

        // Terima penuh 10/10 -> sukses, stok naik, PO -> received
        $this->post('/apps/goods-receipts', $base + [
            'items' => [[
                'purchase_order_item_id' => $poItem->id,
                'item_id' => $item->id,
                'qty_received' => 10,
            ]],
        ])->assertRedirect();

        $this->assertSame(10, $item->refresh()->current_stock);
        $this->assertSame(10, $poItem->refresh()->qty_received);
        $this->assertSame('received', $po->refresh()->status);
        $this->assertDatabaseHas('stock_ins', [
            'reference_no' => 'IN-GR-'.$po->goodsReceipts->first()->gr_number,
            'item_id' => $item->id,
            'qty' => 10,
        ]);

        // Percobaan terima lagi di atas sisa -> ditolak, GR tidak tersimpan, stok tidak berubah
        $this->post('/apps/goods-receipts', $base + [
            'items' => [[
                'purchase_order_item_id' => $poItem->id,
                'item_id' => $item->id,
                'qty_received' => 1,
            ]],
        ])->assertRedirect();

        $this->assertDatabaseCount('goods_receipts', 1); // hanya GR pertama yang tersimpan
        $this->assertSame(10, $item->refresh()->current_stock);
        $this->assertSame(10, $poItem->refresh()->qty_received);
    }
}