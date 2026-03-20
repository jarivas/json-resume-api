<?php

namespace Tests\Feature\Mcp\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_award_index_requires_authentication()
    {
        $response = $this->get('/mcp/award');

        $response->assertStatus(403);
    }

    public function test_mcp_award_index_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/mcp/award');

        $response->assertStatus(403);
    }
}
