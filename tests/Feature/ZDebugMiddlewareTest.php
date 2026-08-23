<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ZDebugMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_a_no_middleware(): void
    {
        $this->withoutMiddleware();
        $this->get('/apps/suppliers')->assertOk();
        $this->post('/apps/suppliers', ['name' => 'Z Debug', 'code' => 'Z-'.uniqid()])->assertRedirect();
        $this->assertTrue(true);
    }
}