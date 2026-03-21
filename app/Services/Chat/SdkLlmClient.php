<?php

namespace App\Services\Chat;

use Laravel\Ai\Ai;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Messages\UserMessage;
use App\Ai\Agents\ResumeAgent;

class SdkLlmClient implements LlmClientInterface
{
    protected $providerOverride;

    public function __construct($providerOverride = null)
    {
        $this->providerOverride = $providerOverride;
    }

    public function generateReply(string $context, string $message, ?array $metadata = null, ?string $sessionId = null): array
    {
        $provider = $this->providerOverride ?? Ai::textProvider();

        // Attach semantic CV context when available (non-invasive, optional).
        try {
            if (class_exists(ResumeAgent::class)) {
                $agent = new ResumeAgent();
                $semantic = $agent->semanticContext($message);
                if ($semantic) {
                    $context = $context ? ($semantic."\n\n".$context) : $semantic;
                }
            }
        } catch (\Throwable $e) {
            // Ignore semantic retrieval failures to avoid breaking chat flow
        }

        $model = $provider->defaultTextModel();

        $messages = [new UserMessage($context ? ($context."\n\n".$message) : $message)];

        $temperature = $metadata['temperature'] ?? null;
        $maxTokens = $metadata['max_tokens'] ?? $metadata['maxTokens'] ?? null;

        $options = new TextGenerationOptions(
            null,
            $maxTokens ? (int) $maxTokens : null,
            $temperature ? (float) $temperature : null,
        );

        $response = $provider->textGateway()->generateText(
            $provider,
            $model,
            null,
            $messages,
            [],
            null,
            $options,
            null,
        );

        $text = (string) $response;

        return [
            'reply' => $text,
            'sources' => [],
            'session_id' => $sessionId ?? bin2hex(random_bytes(8)),
        ];
    }
}
