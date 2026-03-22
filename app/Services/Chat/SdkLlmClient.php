<?php

namespace App\Services\Chat;

use App\Ai\Agents\ResumeAgent;
use Laravel\Ai\Ai;
use Laravel\Ai\Exceptions\RateLimitedException;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Messages\UserMessage;

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
                $agent = new ResumeAgent;
                $semantic = $agent->semanticContext($message);
                if ($semantic) {
                    $context = $context ? ($semantic."\n\n".$context) : $semantic;
                }
            }
        } catch (\Throwable $e) {
            // Ignore semantic retrieval failures to avoid breaking chat flow
        }

        $models = $this->resolveTextModelCandidates($provider);

        $messages = [new UserMessage($context ? ($context."\n\n".$message) : $message)];

        $temperature = $metadata['temperature'] ?? null;
        $maxTokens = $metadata['max_tokens'] ?? $metadata['maxTokens'] ?? null;

        $options = new TextGenerationOptions(
            null,
            $maxTokens ? (int) $maxTokens : null,
            $temperature ? (float) $temperature : null,
        );

        $response = null;
        $lastRateLimitedException = null;

        foreach ($models as $model) {
            try {
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

                break;
            } catch (RateLimitedException $e) {
                $lastRateLimitedException = $e;

                continue;
            }
        }

        if ($response === null) {
            throw $lastRateLimitedException ?? new \RuntimeException('No text model could generate a response.');
        }

        $text = (string) $response;

        return [
            'reply' => $text,
            'sources' => [],
            'session_id' => $sessionId ?? bin2hex(random_bytes(8)),
        ];
    }

    protected function resolveTextModel(object $provider): string
    {
        $providerName = method_exists($provider, 'name')
            ? (string) $provider->name()
            : (string) config('ai.default', 'openai');

        $configuredModel = config("ai.providers.{$providerName}.deployment")
            ?? config("ai.providers.{$providerName}.models.text.default");

        if (is_string($configuredModel) && trim($configuredModel) !== '') {
            return $configuredModel;
        }

        if (method_exists($provider, 'defaultTextModel')) {
            return (string) $provider->defaultTextModel();
        }

        return 'gpt-4o-mini';
    }

    protected function resolveTextModelCandidates(object $provider): array
    {
        $providerName = method_exists($provider, 'name')
            ? (string) $provider->name()
            : (string) config('ai.default', 'openai');

        $alternatives = config("ai.providers.{$providerName}.alternative_deployment", []);

        if (is_string($alternatives) && trim($alternatives) !== '') {
            $alternatives = [$alternatives];
        }

        if (! is_array($alternatives)) {
            $alternatives = [];
        }

        $models = [$this->resolveTextModel($provider)];
        foreach ($alternatives as $alternativeModel) {
            if (is_string($alternativeModel) && trim($alternativeModel) !== '') {
                $models[] = $alternativeModel;
            }
        }

        return array_values(array_unique($models));
    }
}
