<?php

namespace Tests\Feature\Mcp\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_mcp_education_index_accessible_when_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/mcp/education');

        $response->assertStatus(403);
    }
}
