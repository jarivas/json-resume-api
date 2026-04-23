<?php

namespace Tests\Feature\AgentConversation;

use App\Models\AgentConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_conversation_read_ok(): void
    {
        $user = User::factory()->create();

        AgentConversation::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/agent-conversation');
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 3)
            ->has('current_page')
            ->has('per_page')
            ->has('total')
            ->etc()
        );
    }

    public function test_agent_conversation_read_only_returns_own_conversations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        AgentConversation::factory()->count(2)->create(['user_id' => $user->id]);
        AgentConversation::factory()->count(3)->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->getJson('/api/agent-conversation');
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('total', 2)
            ->etc()
        );
    }

    public function test_agent_conversation_read_pagination(): void
    {
        $user = User::factory()->create();

        AgentConversation::factory()->count(20)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/agent-conversation/2');
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('current_page', 2)
            ->has('data', 5)
            ->where('total', 20)
            ->etc()
        );
    }

    public function test_agent_conversation_read_unauthenticated(): void
    {
        $response = $this->getJson('/api/agent-conversation');
        $response->assertUnauthorized();
    }
}
