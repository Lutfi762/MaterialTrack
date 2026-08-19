<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Uji terhadap MySQL dev; semua data test di-rollback otomatis
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.database' => 'materialtrack']);
        DB::connection('mysql')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql')->rollBack();
        config(['database.default' => 'sqlite']);
        parent::tearDown();
    }

    public function test_global_observer_mencatat_crud_item(): void
    {
        $user = User::first();
        $category = Category::first();

        $this->actingAs($user);

        $item = Item::create([
            'category_id' => $category->id,
            'code' => 'AUD-'.now()->timestamp,
            'name' => 'Audit Test Item',
            'unit' => 'pcs',
            'min_stock' => 1,
            'current_stock' => 10,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'items.create',
            'subject_type' => Item::class,
            'subject_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $item->update(['name' => 'Audit Test Item Updated']);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'items.update',
            'subject_id' => $item->id,
        ]);

        $item->delete();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'items.delete',
            'subject_id' => $item->id,
        ]);
    }

public function test_event_login_dan_failed_tercatat(): void
    {
        $user = User::first();

        $this->actingAs($user);

        Event::dispatch(new Login('web', $user, false));

        $this->assertDatabaseHas('activity_logs', [
            'action'  => 'auth.login',
            'user_id' => $user->id,
        ]);

        Event::dispatch(new Failed('web', null, ['email' => 'audit@test.local']));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'auth.login_failed',
        ]);
    }

    public function test_halaman_audit_baru_render(): void
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();

        $this->actingAs($admin);

        $this->get('/apps/stock-adjustments')->assertOk();
        $this->get('/apps/activity-logs')->assertOk();
        $this->get('/apps/stock-adjustments/create')->assertOk();
        $this->get('/apps/stock-opnames')->assertOk();
    }
}
