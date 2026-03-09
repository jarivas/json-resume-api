<?php

namespace App\Services\Chat;

use Laravel\Ai\Ai;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Messages\UserMessage;

class SdkLlmClient implements LlmClientInterface
{
    public function generateReply(string $context, string $message, ?array $metadata = null, ?string $sessionId = null): array
    {
        $provider = Ai::textProvider();

        $model = $provider->defaultTextModel();

        $messages = [new UserMessage($context ? ($context . "\n\n" . $message) : $message)];

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
