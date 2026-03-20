<?php

namespace Tests\Feature\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_endpoint_returns_reply_and_session()
    {
        ResumeAgent::fake(['echo: Hello, I am a resume assistant.']);

        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced developer',
        ]);
        // Ensure DB records are created
        $this->assertDatabaseCount('ai_requests', 0);
        $resp = $this->postJson('/api/chat', [
            'message' => 'Tell me about Test User experience',
        ]);

        $resp->assertStatus(200)
            ->assertJsonStructure(['reply', 'sources', 'session_id']);

        $this->assertStringStartsWith('echo: ', $resp->json('reply'));

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'message' => 'Tell me about Test User experience',
        ]);
    }
}
