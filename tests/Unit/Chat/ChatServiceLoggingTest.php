<?php

namespace Tests\Unit\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Tests\TestCase;
use Throwable;

class ChatServiceLoggingTest extends TestCase
{
    public function test_it_logs_context_when_the_chat_service_is_rate_limited(): void
    {
        Log::spy();

        $service = $this->makeFailureService(new RateLimitedException('Rate limit reached', 429));

        $response = $service->reply(
            'Contactame en test@example.com para revisar el CV.',
            'session-rate-limit',
            ['language' => 'es', 'contact' => '+34 600 123 456']
        );

        $this->assertSame(
            'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
            $response['reply']
        );

        Log::shouldHaveReceived('warning')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Chat service rate limited.'
                    && ($context['session_id'] ?? null) === 'session-rate-limit'
                    && ($context['exception_class'] ?? null) === RateLimitedException::class
                    && ($context['exception_code'] ?? null) === 429
                    && ($context['message_preview'] ?? null) === 'Contactame en [REDACTED_EMAIL] para revisar el CV.'
                    && ($context['metadata']['contact'] ?? null) === '[REDACTED_PHONE]';
            })
            ->once();
    }

    public function test_it_logs_context_when_the_chat_service_has_a_provider_error(): void
    {
        Log::spy();

        $service = $this->makeFailureService(new AiException('Provider unavailable', 503));

        $response = $service->reply(
            'Quiero optimizar mi CV para una vacante backend.',
            'session-provider-error',
            ['locale' => 'es-ES']
        );

        $this->assertSame(
            'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.',
            $response['reply']
        );

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Chat service provider error.'
                    && ($context['session_id'] ?? null) === 'session-provider-error'
                    && ($context['exception_class'] ?? null) === AiException::class
                    && ($context['exception_code'] ?? null) === 503
                    && ($context['metadata']['provider_error'] ?? null) === true
                    && ($context['metadata']['provider_error_message'] ?? null) === 'Provider unavailable';
            })
            ->once();
    }

    protected function makeFailureService(Throwable $exception): ChatService
    {
        return new class($exception) extends ChatService
        {
            public function __construct(private Throwable $failureException)
            {
                parent::__construct();
            }

            protected function buildContext(string $message, ResumeAgent $agent): string
            {
                return 'Summary: Backend engineer profile';
            }

            protected function createAgent(): ResumeAgent
            {
                return new class extends ResumeAgent
                {
                    public function __construct()
                    {
                        parent::__construct('Test instructions', []);
                    }

                    public function textModelCandidates(?string $provider = null): array
                    {
                        return ['model-primary', 'model-fallback'];
                    }
                };
            }

            protected function promptWithRecovery(ResumeAgent $agent, string $userPrompt): mixed
            {
                throw $this->failureException;
            }

            protected function storeFailureRequest(string $context, string $message, string $reply, ?string $sessionId, array $metadata): void {}
        };
    }
}
