<?php

namespace Tests\Feature\Mcp\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AwardHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_award_get_is_not_forbidden_without_authentication()
    {
        $response = $this->get('/mcp/award');

        $response->assertStatus(405);
    }

    public function test_mcp_award_get_is_not_forbidden_when_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/mcp/award');

        $response->assertStatus(405);
    }
}
