<?php

namespace Tests\Feature\Mcp\Http;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class EducationHttpTest extends TestCase
{
    public function test_mcp_education_index_accessible_when_authenticated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get('/mcp/education');

        $response->assertStatus(200);
    }
}
