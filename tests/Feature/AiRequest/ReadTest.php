<?php

namespace Tests\Feature\AiRequest;

use App\Models\AiRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_request_read_ok(): void
    {
        $user = User::factory()->create();

        AiRequest::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/ai-request');
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 3)
            ->has('current_page')
            ->has('per_page')
            ->has('total')
            ->etc()
        );
    }

    public function test_ai_request_read_pagination(): void
    {
        $user = User::factory()->create();

        AiRequest::factory()->count(20)->create();

        $response = $this->actingAs($user)->getJson('/api/ai-request/2');
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('current_page', 2)
            ->has('data', 5)
            ->where('total', 20)
            ->etc()
        );
    }

    public function test_ai_request_read_unauthenticated(): void
    {
        $response = $this->getJson('/api/ai-request');
        $response->assertUnauthorized();
    }
}
