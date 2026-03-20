<?php

namespace Tests\Feature\Mcp;

use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_items_and_meta()
    {
        $basic = Basic::factory()->create();

        $ref = new \ReflectionClass(\App\Mcp\Servers\BasicServer::class);
        $srv = $ref->newInstanceWithoutConstructor();

        $result = $srv->index(['per_page' => 10, 'page' => 1]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);

        $this->assertCount(1, $result['data']);

        $item = $result['data'][0];

        $this->assertArrayHasKey('id', $item);
        $this->assertSame($basic->id, $item['id']);
        $this->assertSame($basic->name, $item['name']);

        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals(10, $result['meta']['per_page']);
    }

    public function test_show_returns_item_by_id()
    {
        $basic = Basic::factory()->create();

        $ref = new \ReflectionClass(\App\Mcp\Servers\BasicServer::class);
        $srv = $ref->newInstanceWithoutConstructor();

        $result = $srv->show($basic->id);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame($basic->id, $result['id']);
    }
}
