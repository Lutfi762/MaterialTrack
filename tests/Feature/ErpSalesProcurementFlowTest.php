<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression test untuk temuan AUDIT_ERP_MODULES_2026-08-19.md:
 * - CRITICAL #5 (Delivery over-ship), #6 (double-invoice per PO/SO), #8 (race nomor dokumen)
 * Flow Sales (Quotation -> SO -> Delivery -> SalesInvoice) & Procurement (PR -> PO -> Invoice)
 * belum punya test end-to-end sebelum ini.
 */
class ErpSalesProcurementFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'materialtrack']);
        config(['session.driver' => 'array']);
        DB::connection('mysql')->beginTransaction();

        // Ganti CSRF middleware jadi no-op, TAPI SubstituteBindings (route model
        // binding) tetap aktif — withoutMiddleware() global mematikannya dan
        // membuat {salesOrder}/{purchaseOrder} tidak ter-resolve saat testing.
        $noop = new class(
            $this->app,
            $this->app->make(\Illuminate\Contracts\Encryption\Encrypter::class),
        ) extends PreventRequestForgery {
            public function handle($request, \Closure $next)
            {
                return $next($request);
            }
        };
        $this->app->instance(PreventRequestForgery::class, $noop);
        $this->app->instance(ValidateCsrfToken::class, $noop);
        $this->app->instance(VerifyCsrfToken::class, $noop);

        $this->actingAs($this->admin());
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
            'code' => 'ERP-'.uniqid(),
            'name' => 'ERP Test Item '.uniqid(),
            'unit' => 'pcs',
            'min_stock' => 1,
            'current_stock' => $stock,
        ]);
    }

    public function test_sales_flow_quotation_to_invoice_dan_anti_double_invoice(): void
    {
        $item = $this->makeItem(20);
        $customer = Customer::create(['name' => 'Customer Test', 'code' => 'CUST-'.uniqid()]);

        $this->post('/apps/quotations', [
            'customer_id' => $customer->id,
            'items' => [['item_id' => $item->id, 'qty' => 5, 'unit_price' => 1000]],
        ])->assertRedirect();
        $quotation = DB::table('quotations')->where('customer_id', $customer->id)->first();
        $this->assertNotNull($quotation);

        $this->post('/apps/sales-orders', [
            'customer_id' => $customer->id,
            'quotation_id' => $quotation->id,
            'items' => [['item_id' => $item->id, 'qty' => 5, 'unit_price' => 1000]],
        ])->assertRedirect();
        $so = DB::table('sales_orders')->where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($so);
        $this->assertSame('pending', $so->status, 'SO baru harus berstatus pending sebelum approve');

        $this->post("/apps/sales-orders/{$so->id}/approve")->assertRedirect();
        $this->assertSame('approved', DB::table('sales_orders')->find($so->id)->status);

        $soItem = DB::table('sales_order_items')->where('sales_order_id', $so->id)->first();

        $this->post('/apps/deliveries', [
            'sales_order_id' => $so->id,
            'delivery_date' => now()->toDateString(),
            'items' => [['sales_order_item_id' => $soItem->id, 'item_id' => $item->id, 'qty_shipped' => 5]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(15, $item->refresh()->current_stock);
        $this->assertSame('delivered', DB::table('sales_orders')->find($so->id)->status);

        // Over-ship: sisa qty sudah 0 -> harus ditolak (CRITICAL #5)
        $this->post('/apps/deliveries', [
            'sales_order_id' => $so->id,
            'delivery_date' => now()->toDateString(),
            'items' => [['sales_order_item_id' => $soItem->id, 'item_id' => $item->id, 'qty_shipped' => 1]],
        ])->assertRedirect();
        $this->assertDatabaseCount('deliveries', 1);
        $this->assertSame(15, $item->refresh()->current_stock);

        $this->post('/apps/sales-invoices', [
            'sales_order_id' => $so->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('sales_invoices', ['sales_order_id' => $so->id]);

        // Faktur kedua untuk SO sama -> harus ditolak (CRITICAL #6 anti double-invoice)
        $this->post('/apps/sales-invoices', [
            'sales_order_id' => $so->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseCount('sales_invoices', 1);
    }

    public function test_procurement_flow_pr_to_invoice_dan_anti_double_invoice(): void
    {
        $item = $this->makeItem(0);
        $supplier = Supplier::create(['name' => 'Supplier Test', 'code' => 'SUP-'.uniqid()]);
        $department = DB::table('departments')->first();

        $this->post('/apps/purchase-requests', [
            'department_id' => $department->id,
            'priority' => 'medium',
            'items' => [['item_id' => $item->id, 'qty' => 10, 'estimated_price' => 500]],
        ])->assertRedirect();
        $pr = DB::table('purchase_requests')->orderByDesc('id')->first();
        $this->post("/apps/purchase-requests/{$pr->id}/approve")->assertRedirect();

        $this->post('/apps/purchase-orders', [
            'supplier_id' => $supplier->id,
            'purchase_request_id' => $pr->id,
            'items' => [['item_id' => $item->id, 'qty' => 10, 'unit_price' => 500]],
        ])->assertRedirect();
        $po = PurchaseOrder::where('purchase_request_id', $pr->id)->firstOrFail();
        $this->post("/apps/purchase-orders/{$po->id}/approve")->assertRedirect();

        $this->post('/apps/purchase-invoices', [
            'purchase_order_id' => $po->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('purchase_invoices', ['purchase_order_id' => $po->id]);

        // Faktur kedua untuk PO sama -> harus ditolak (CRITICAL #6)
        $this->post('/apps/purchase-invoices', [
            'purchase_order_id' => $po->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseCount('purchase_invoices', 1);
    }

    public function test_document_number_unik_tanpa_bentrok(): void
    {
        $customer = Customer::create(['name' => 'Customer Nomor', 'code' => 'CUST-'.uniqid()]);
        $item = $this->makeItem(100);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/apps/quotations', [
                'customer_id' => $customer->id,
                'items' => [['item_id' => $item->id, 'qty' => 1, 'unit_price' => 100]],
            ])->assertRedirect();
        }

        $numbers = DB::table('quotations')->where('customer_id', $customer->id)->pluck('quotation_number');
        $this->assertCount(3, $numbers);
        $this->assertCount(3, $numbers->unique());
    }
}
