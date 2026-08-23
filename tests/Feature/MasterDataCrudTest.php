<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Uji terhadap MySQL dev; semua data test di-rollback otomatis
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'materialtrack']);
        // Hindari deadlock lock file session antar request test berurutan
        config(['session.driver' => 'array']);
        DB::connection('mysql')->beginTransaction();

        // Laravel 11: `withoutMiddleware(VerifyCsrfToken::class)` tidak mempan
        // (framework sekarang pakai ValidateCsrfToken). Ganti instance CSRF jadi no-op.
        // Substitusi model binding (SubstituteBindings) TETAP aktif.
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

    public function test_supplier_crud(): void
    {
        $code = 'SUP-'.uniqid();

        $this->get('/apps/suppliers')->assertOk();
        $this->get('/apps/suppliers/create')->assertOk();

        $this->post('/apps/suppliers', [
            'name' => 'Supplier Test',
            'code' => $code,
            'phone' => '0812-000',
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['code' => $code]);
        $id = DB::table('suppliers')->where('code', $code)->value('id');

        $this->get("/apps/suppliers/{$id}/edit")->assertOk();

        $this->put("/apps/suppliers/{$id}", [
            'name' => 'Supplier Test Updated',
            'code' => $code,
            'is_active' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['id' => $id, 'name' => 'Supplier Test Updated', 'is_active' => false]);

        $this->delete("/apps/suppliers/{$id}")->assertRedirect();
        $this->assertDatabaseMissing('suppliers', ['id' => $id]);
    }

    public function test_category_crud(): void
    {
        $suffix = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 4);
        $prefix = 'CT'.$suffix;

        $this->get('/apps/categories')->assertOk();
        $this->get('/apps/categories/create')->assertOk();

        $this->post('/apps/categories', [
            'name' => 'Kategori Test',
            'code_prefix' => $prefix,
            'description' => 'Deskripsi test',
            'department_ids' => [],
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['code_prefix' => strtoupper($prefix)]);
        $id = DB::table('categories')->where('code_prefix', strtoupper($prefix))->value('id');

        $this->get("/apps/categories/{$id}/edit")->assertOk();

        $this->put("/apps/categories/{$id}", [
            'name' => 'Kategori Test Updated',
            'code_prefix' => $prefix,
            'description' => null,
            'department_ids' => [],
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $id, 'name' => 'Kategori Test Updated']);

        $this->delete("/apps/categories/{$id}")->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $id]);
    }

    public function test_item_crud(): void
    {
        // Kategori baru dari factory agar auto-generate kode item deterministik
        $category = Category::create([
            'name' => 'Kategori Item Test',
            'code_prefix' => 'IT'.substr(uniqid(), -4),
        ]);

        $this->get('/apps/items')->assertOk();
        $this->get('/apps/items/create')->assertOk();

        $this->post('/apps/items', [
            'category_id' => $category->id,
            'name' => 'Material Test',
            'description' => 'Deskripsi material',
            'unit' => 'PCS',
            'min_stock' => 1,
            'current_stock' => 5,
            'price' => 1000,
        ])->assertRedirect();

        $this->assertDatabaseHas('items', ['name' => 'Material Test', 'category_id' => $category->id]);
        $item = Category::find($category->id)->items()->firstOrFail();
        // Kode material di-generate otomatis dari code_prefix kategori
        $this->assertSame($category->code_prefix.'-0001', $item->code);

        $this->get("/apps/items/{$item->id}/edit")->assertOk();

        $this->put("/apps/items/{$item->id}", [
            'category_id' => $category->id,
            'name' => 'Material Test Updated',
            'unit' => 'PCS',
            'min_stock' => 2,
        ])->assertRedirect();

        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'Material Test Updated', 'min_stock' => 2]);

        $this->delete("/apps/items/{$item->id}")->assertRedirect();
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_item_list_cache_invalidated_on_create_and_delete(): void
    {
        config(['cache.default' => 'array']);

        $category = Category::firstOrFail();

        // Panaskan cache list (scope 'all')
        CacheService::getItemsList(null, null, null, 1, null);

        $this->post('/apps/items', [
            'category_id' => $category->id,
            'name' => 'Material Cache Test',
            'description' => 'Test invalidasi cache',
            'unit' => 'PCS',
            'min_stock' => 1,
            'current_stock' => 5,
        ])->assertRedirect();

        $item = $category->items()->orderByDesc('id')->firstOrFail();

        // Item baru harus langsung muncul — cache list tidak boleh basi
        $items = CacheService::getItemsList(null, null, null, 1, null)['items'];
        $this->assertTrue(collect($items)->contains('id', $item->id));

        $this->delete("/apps/items/{$item->id}")->assertRedirect();
        $this->assertDatabaseMissing('items', ['id' => $item->id]);

        // Item terhapus harus langsung hilang dari list yang sudah di-cache ulang
        $items = CacheService::getItemsList(null, null, null, 1, null)['items'];
        $this->assertFalse(collect($items)->contains('id', $item->id));
    }

    public function test_location_crud_and_map(): void
    {
        $code = 'LOC-'.uniqid();

        $this->get('/apps/locations')->assertOk();
        $this->get('/apps/locations/create')->assertOk();

        $this->post('/apps/locations', [
            'name' => 'Lokasi Test',
            'code' => $code,
            'address' => 'Jl. Test No. 1',
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', ['code' => $code]);
        $id = DB::table('locations')->where('code', $code)->value('id');

        $this->get("/apps/locations/{$id}/edit")->assertOk();
        // Peta gudang tidak boleh error
        $this->get('/apps/locations/map')->assertOk();

        $this->put("/apps/locations/{$id}", [
            'name' => 'Lokasi Test Updated',
            'code' => $code,
            'address' => 'Jl. Baru No. 2',
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', ['id' => $id, 'name' => 'Lokasi Test Updated']);

        $this->delete("/apps/locations/{$id}")->assertRedirect();
        $this->assertDatabaseMissing('locations', ['id' => $id]);
    }

    public function test_warehouse_crud(): void
    {
        $code = 'WH-'.uniqid();

        $this->get('/apps/warehouses')->assertOk();
        $this->get('/apps/warehouses/create')->assertOk();

        $this->post('/apps/warehouses', [
            'name' => 'Gudang Test',
            'code' => $code,
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('warehouses', ['code' => $code]);
        $id = DB::table('warehouses')->where('code', $code)->value('id');

        $this->get("/apps/warehouses/{$id}/edit")->assertOk();

        $this->put("/apps/warehouses/{$id}", [
            'name' => 'Gudang Test Updated',
            'code' => $code,
            'is_active' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('warehouses', ['id' => $id, 'name' => 'Gudang Test Updated', 'is_active' => false]);

        $this->delete("/apps/warehouses/{$id}")->assertRedirect();
        $this->assertDatabaseMissing('warehouses', ['id' => $id]);
    }
}