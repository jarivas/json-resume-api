<?php

namespace Tests\Feature\Mcp\Http;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AwardHttpTest extends TestCase
{
    public function test_mcp_award_index_requires_authentication()
    {
        $response = $this->get('/mcp/award');

        $response->assertStatus(302);
    }

    public function test_mcp_award_index_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get('/mcp/award');

        $response->assertStatus(200);
    }
}
