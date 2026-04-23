<?php

namespace Tests\Feature\AgentConversation;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_conversation_read_messages_ok(): void
    {
        $user = User::factory()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

        AgentConversationMessage::factory()->count(4)->create(['conversation_id' => $conversation->id]);

        $response = $this->actingAs($user)->getJson("/api/agent-conversation/{$conversation->id}/messages");
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 4)
            ->has('current_page')
            ->has('per_page')
            ->has('total')
            ->etc()
        );
    }

    public function test_agent_conversation_read_messages_pagination(): void
    {
        $user = User::factory()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

        AgentConversationMessage::factory()->count(20)->create(['conversation_id' => $conversation->id]);

        $response = $this->actingAs($user)->getJson("/api/agent-conversation/{$conversation->id}/messages/2");
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('current_page', 2)
            ->has('data', 5)
            ->where('total', 20)
            ->etc()
        );
    }

    public function test_agent_conversation_read_messages_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conversation = AgentConversation::factory()->create(['user_id' => $other->id]);

        $response = $this->actingAs($user)->getJson("/api/agent-conversation/{$conversation->id}/messages");
        $response->assertForbidden();
    }

    public function test_agent_conversation_read_messages_unauthenticated(): void
    {
        $conversation = AgentConversation::factory()->create();

        $response = $this->getJson("/api/agent-conversation/{$conversation->id}/messages");
        $response->assertUnauthorized();
    }
}
