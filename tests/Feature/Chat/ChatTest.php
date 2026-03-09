<?php

namespace Tests\Feature\Chat;

use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_endpoint_returns_reply_and_session()
    {
        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced developer',
        ]);

        $resp = $this->postJson('/api/chat', [
            'message' => 'Hello, who are you?',
        ]);

        $resp->assertStatus(200)
            ->assertJsonStructure(['reply', 'sources', 'session_id']);

        $this->assertStringStartsWith('echo: ', $resp->json('reply'));
    }
}
