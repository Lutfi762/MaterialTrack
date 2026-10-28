<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Location;
use App\Models\Department;

class NewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionsTableSeeder']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_fixed_asset_pages_load()
    {
        $response = $this->actingAs($this->user)->get('/apps/fixed-assets');
        $response->assertStatus(200);
    }

    public function test_budget_pages_load()
    {
        $response = $this->actingAs($this->user)->get('/apps/budgets');
        $response->assertStatus(200);
    }

    public function test_supplier_scorecard_pages_load()
    {
        $response = $this->actingAs($this->user)->get('/apps/supplier-scorecards');
        $response->assertStatus(200);
    }

    public function test_reorder_pr_generation_command()
    {
        Department::create(['name' => 'Test Department']);

        $category = Category::create(['name' => 'Test Category']);
        $location = Location::create(['name' => 'Test Location']);
        $item = Item::create([
            'category_id' => $category->id,
            'location_id' => $location->id,
            'code' => 'TEST-001',
            'name' => 'Test Item',
            'unit' => 'pcs',
            'price' => 1000,
            'current_stock' => 5,
            'min_stock' => 0,
            'reorder_point' => 10,
            'reorder_qty' => 50,
        ]);

        $this->artisan('reorder:generate-pr')
             ->expectsOutputToContain('Generated auto-PR')
             ->assertExitCode(0);

        $this->assertDatabaseHas('purchase_requests', [
            'status' => 'draft'
        ]);
    }
}
