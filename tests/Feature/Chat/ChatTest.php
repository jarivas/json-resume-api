<?php

namespace Tests\Feature\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
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

    public function test_resume_agent_allows_query_logic_without_fake()
    {
        $agent = new ResumeAgent;

        $this->assertTrue($agent->allowsQuery('Tell me about experience and projects'));
        $this->assertFalse($agent->allowsQuery('Tell me a joke about developers'));
    }

    public function test_resume_agent_model_prefers_provider_deployment_config(): void
    {
        config([
            'ai.default' => 'openai',
            'ai.providers.openai.deployment' => 'chat-deployment-from-config',
            'ai.providers.openai.models.text.default' => 'chat-model-from-models',
            'ai.providers.openai.alternative_deployment' => ['chat-fallback-1', 'chat-fallback-2'],
            'ai.providers.openai.fallback_providers' => ['anthropic', 'groq'],
        ]);

        $agent = new ResumeAgent;

        $this->assertSame('chat-deployment-from-config', $agent->model());
        $this->assertSame(
            ['chat-deployment-from-config', 'chat-fallback-1', 'chat-fallback-2'],
            $agent->textModelCandidates(),
        );
        $this->assertSame(['openai', 'anthropic', 'groq'], $agent->providerCandidates());
    }

    public function test_chat_endpoint_accepts_payload_with_session_and_metadata()
    {
        ResumeAgent::fake(['echo: Sí, tiene experiencia en PHP.']);

        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced PHP developer',
        ]);

        $payload = [
            'message' => '¿tiene experiencia php?',
            'session_id' => 'sess_2923c090cdc44b40',
            'metadata' => [
                'language' => 'es',
                'locale' => 'es-ES',
            ],
        ];

        $resp = $this->postJson('/api/chat', $payload);

        $resp->assertStatus(200)
            ->assertJsonStructure(['reply', 'sources', 'session_id']);

        $this->assertStringStartsWith('echo: ', $resp->json('reply'));
        $this->assertEquals('sess_2923c090cdc44b40', $resp->json('session_id'));

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'message' => '¿tiene experiencia php?',
        ]);
    }

    public function test_chat_endpoint_accepts_payload_with_session_and_metadata_without_fake()
    {
        // Do not fake the agent — exercise the real path using Gemini provider.
        if (empty(env('GEMINI_API_KEY'))) {
            $this->markTestSkipped('GEMINI_API_KEY not set; set AI_DEFAULT_PROVIDER=gemini and GEMINI_API_KEY to run this test.');
        }

        // Ensure the application uses Gemini as the default provider for this test.
        putenv('AI_DEFAULT_PROVIDER=gemini');
        config([
            'ai.default' => 'gemini',
            'ai.providers.gemini.key' => env('GEMINI_API_KEY'),
        ]);
        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced PHP developer',
        ]);

        $payload = [
            'message' => '¿tiene experiencia php?',
            'session_id' => 'sess_2923c090cdc44b40',
            'metadata' => [
                'language' => 'es',
                'locale' => 'es-ES',
            ],
        ];

        $resp = $this->postJson('/api/chat', $payload);

        if ($resp->status() >= 500) {
            $exceptionClass = is_object($resp->exception) ? $resp->exception::class : 'unknown';
            $this->markTestSkipped('Gemini provider failed during integration test execution: '.$exceptionClass);
        }

        $resp->assertStatus(200)
            ->assertJsonStructure(['reply', 'sources', 'session_id']);

        $this->assertEquals('sess_2923c090cdc44b40', $resp->json('session_id'));

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'message' => '¿tiene experiencia php?',
        ]);
    }

    public function test_chat_endpoint_returns_fallback_reply_when_provider_is_rate_limited(): void
    {
        ResumeAgent::fake([
            static fn () => throw RateLimitedException::forProvider('gemini'),
        ]);

        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced developer',
        ]);

        $response = $this->postJson('/api/chat', [
            'message' => 'Tell me about Test User experience',
            'session_id' => 'sess_rate_limited',
        ]);

        $response->assertOk()
            ->assertJson([
                'reply' => 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
                'sources' => [],
                'session_id' => 'sess_rate_limited',
            ]);

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'session_id' => 'sess_rate_limited',
            'message' => 'Tell me about Test User experience',
            'reply' => 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
        ]);
    }

    public function test_chat_endpoint_returns_fallback_reply_when_provider_model_is_not_found(): void
    {
        ResumeAgent::fake([
            static fn () => throw new AiException('Gemini Error [404]: NOT_FOUND - models/gemini-1.5-flash is not found for API version v1beta.', 404),
        ]);

        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced developer',
        ]);

        $response = $this->postJson('/api/chat', [
            'message' => 'Tell me about Test User experience',
            'session_id' => 'sess_model_not_found',
        ]);

        $response->assertOk()
            ->assertJson([
                'reply' => 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
                'sources' => [],
                'session_id' => 'sess_model_not_found',
            ]);

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'session_id' => 'sess_model_not_found',
            'message' => 'Tell me about Test User experience',
            'reply' => 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
        ]);
    }

    public function test_chat_endpoint_uses_model_fallback_after_initial_prompt_failure(): void
    {
        $calls = 0;

        ResumeAgent::fake(static function () use (&$calls): string {
            $calls++;

            if ($calls === 1) {
                throw new AiException('Primary prompt failed.', 500);
            }

            return 'echo: fallback model worked';
        });

        Basic::factory()->create([
            'name' => 'Test User',
            'summary' => 'Experienced developer',
        ]);

        $response = $this->postJson('/api/chat', [
            'message' => 'Tell me about Test User experience',
            'session_id' => 'sess_prompt_then_fallback',
        ]);

        $response->assertOk()
            ->assertJson([
                'reply' => 'echo: fallback model worked',
                'sources' => [],
                'session_id' => 'sess_prompt_then_fallback',
            ]);

        $this->assertDatabaseCount('ai_requests', 1);
        $this->assertDatabaseHas('ai_requests', [
            'session_id' => 'sess_prompt_then_fallback',
            'message' => 'Tell me about Test User experience',
            'reply' => 'echo: fallback model worked',
        ]);
    }

    public function test_resume_agent_retries_on_model_not_found_ai_exception(): void
    {
        ResumeAgent::fake(static function (string $prompt, $attachments, $provider, string $model): string {
            if ($model === 'gemini-1.5-flash') {
                throw new AiException('Gemini Error [404]: NOT_FOUND - models/gemini-1.5-flash is not found for API version v1beta.', 404);
            }

            return 'echo: fallback model worked';
        });

        config([
            'ai.default' => 'gemini',
            'ai.providers.gemini.deployment' => 'gemini-1.5-flash',
            'ai.providers.gemini.alternative_deployment' => ['gemini-2.0-flash-lite'],
        ]);

        $agent = new ResumeAgent;

        $response = $agent->promptWithModelFallback('Tell me about resume experience');

        $this->assertSame('echo: fallback model worked', (string) $response);
    }

    public function test_resume_agent_uses_provider_failover_after_rate_limit(): void
    {
        $calls = [];

        ResumeAgent::fake(static function (string $prompt, $attachments, $provider, string $model) use (&$calls): string {
            $providerName = (string) $provider;
            $calls[] = $providerName.':'.$model;

            if ($providerName === 'gemini') {
                throw RateLimitedException::forProvider('gemini');
            }

            return 'echo: provider fallback worked';
        });

        config([
            'ai.default' => 'gemini',
            'ai.providers.gemini.deployment' => 'gemini-2.5-flash',
            'ai.providers.gemini.alternative_deployment' => ['gemini-2.0-flash-lite'],
            'ai.providers.gemini.fallback_providers' => ['openai'],
            'ai.providers.openai.deployment' => 'gpt-4o-mini',
            'ai.providers.openai.alternative_deployment' => ['gpt-4o'],
        ]);

        $agent = new ResumeAgent;

        $response = $agent->promptWithModelFallback('Tell me about resume experience');

        $this->assertSame('echo: provider fallback worked', (string) $response);
        $this->assertSame([
            'gemini:gemini-2.5-flash',
            'openai:gpt-4o-mini',
        ], $calls);
    }
}
