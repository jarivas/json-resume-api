<?php

namespace App\Services\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Models\AiRequest;
use App\Models\Basic;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Throwable;

class ChatService
{
    protected const string RATE_LIMIT_REPLY = 'El servicio de chat no esta disponible en este momento. Intenta nuevamente en unos minutos.';

    public function __construct() {}

    public function reply(string $message, ?string $sessionId = null, ?array $metadata = null): array
    {
        $context = $this->buildContext();
        $agent = $this->createAgent();

        if (! $this->isQueryAllowed($agent, $message)) {
            return $this->handleDisallowedQuery($message, $context, $sessionId, $metadata);
        }

        $userPrompt = $this->assembleUserPrompt($context, $message);

        try {
            $response = $this->promptWithRecovery($agent, $userPrompt);
        } catch (RateLimitedException $exception) {
            return $this->handleRateLimitedFailure($agent, $context, $message, $sessionId, $metadata, $exception);
        } catch (AiException $exception) {
            return $this->handleProviderErrorFailure($agent, $context, $message, $sessionId, $metadata, $exception);
        }

        $replyText = (string) $response;

        $this->logAiRequest(
            $context,
            $message,
            $replyText,
            $response,
            $sessionId,
            $metadata
        );

        return [
            'reply' => $replyText,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function promptWithRecovery(ResumeAgent $agent, string $userPrompt): mixed
    {
        try {
            return $agent->prompt($userPrompt);
        } catch (Throwable $exception) {
            Log::warning('Chat primary prompt failed. Trying model fallback.', [
                'provider' => (string) config('ai.default'),
                'models' => $this->safeModelCandidates($agent),
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
            ]);

            return $agent->promptWithModelFallback($userPrompt);
        }
    }

    protected function handleRateLimitedFailure(ResumeAgent $agent, string $context, string $message, ?string $sessionId, ?array $metadata, RateLimitedException $exception): array
    {
        $reply = self::RATE_LIMIT_REPLY;
        $safeMetadata = array_merge($metadata ?? [], [
            'rate_limited' => true,
            'models' => $agent->textModelCandidates(),
        ]);

        Log::warning('Chat service rate limited.', [
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'models' => $this->safeModelCandidates($agent),
            'exception_class' => $exception::class,
            'exception_code' => $exception->getCode(),
            'exception_message' => $exception->getMessage(),
            'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            'metadata' => $this->maskValue($safeMetadata),
        ]);

        $this->storeFailureRequest(
            $context,
            $message,
            $reply,
            $sessionId,
            $safeMetadata
        );

        return [
            'reply' => $reply,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function handleProviderErrorFailure(ResumeAgent $agent, string $context, string $message, ?string $sessionId, ?array $metadata, AiException $exception): array
    {
        $reply = self::RATE_LIMIT_REPLY;
        $safeMetadata = array_merge($metadata ?? [], [
            'provider_error' => true,
            'provider_error_code' => $exception->getCode(),
            'provider_error_message' => $exception->getMessage(),
            'models' => $agent->textModelCandidates(),
        ]);

        Log::error('Chat service provider error.', [
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'models' => $this->safeModelCandidates($agent),
            'exception_class' => $exception::class,
            'exception_code' => $exception->getCode(),
            'exception_message' => $exception->getMessage(),
            'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            'metadata' => $this->maskValue($safeMetadata),
        ]);

        $this->storeFailureRequest(
            $context,
            $message,
            $reply,
            $sessionId,
            $safeMetadata
        );

        return [
            'reply' => $reply,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function storeFailureRequest(string $context, string $message, string $reply, ?string $sessionId, array $metadata): void
    {
        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => (string) config('ai.default'),
            'prompt' => $this->maskValue($context),
            'message' => $this->maskValue($message),
            'reply' => $this->maskValue($reply),
            'metadata' => $this->maskValue($metadata),
        ]);
    }

    protected function buildContext(): string
    {
        $basic = Basic::query()->first();

        $contextParts = [];
        if ($basic) {
            if (! empty($basic->summary)) {
                $contextParts[] = 'Summary: '.$basic->summary;
            }
            if (! empty($basic->name)) {
                $contextParts[] = 'Name: '.$basic->name;
            }
        }

        return implode("\n", $contextParts);
    }

    protected function createAgent(): ResumeAgent
    {
        $agentInstructions = "You are a helpful assistant that answers questions about a user's resume. Use the provided resume context when relevant and be concise.";

        return new ResumeAgent($agentInstructions, []);
    }

    protected function isQueryAllowed(ResumeAgent $agent, string $message): bool
    {
        try {
            if (method_exists($agent, 'allowsQuery')) {
                return $agent->allowsQuery($message);
            }
        } catch (Throwable $exception) {
            Log::warning('Chat query validation failed. Allowing query by default.', [
                'provider' => (string) config('ai.default'),
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
                'message_preview' => $this->truncateForLog((string) $this->maskValue($message)),
            ]);
        }

        return true;
    }

    protected function handleDisallowedQuery(string $message, string $context, ?string $sessionId, ?array $metadata): array
    {
        $refusal = 'Lo siento, sólo puedo responder preguntas relacionadas con el CV.';

        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'prompt' => $this->maskValue($context),
            'message' => $this->maskValue($message),
            'reply' => $this->maskValue($refusal),
            'metadata' => $this->maskValue(array_merge($metadata ?? [], ['blocked' => true])),
        ]);

        return [
            'reply' => $refusal,
            'sources' => [],
            'session_id' => $sessionId,
        ];
    }

    protected function assembleUserPrompt(string $context, string $message): string
    {
        return ($context ? ($context."\n\n") : '').$message;
    }

    protected function extractUsage(mixed $response): ?array
    {
        try {
            $usageObj = $response->usage ?? null;
            if ($usageObj) {
                return [
                    'prompt_tokens' => $usageObj->prompt_tokens ?? null,
                    'completion_tokens' => $usageObj->completion_tokens ?? null,
                    'total_tokens' => $usageObj->total_tokens ?? null,
                ];
            }
        } catch (Throwable) {
            // If usage extraction fails, return null
        }

        return null;
    }

    protected function logAiRequest(string $context, string $message, string $reply, mixed $response, ?string $sessionId, ?array $metadata): void
    {
        $usage = $this->extractUsage($response);

        $maskedPrompt = $this->maskValue($context);
        $maskedMessage = $this->maskValue($message);
        $maskedReply = $this->maskValue($reply);
        $maskedMetadata = $this->maskValue(array_merge($metadata ?? [], ['usage' => $usage]));

        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'prompt' => $maskedPrompt,
            'message' => $maskedMessage,
            'reply' => $maskedReply,
            'metadata' => $maskedMetadata,
        ]);
    }

    /**
     * Mask sensitive values recursively.
     * Strings: mask emails, phones, urls.
     */
    protected function maskValue(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return $this->maskString($value);
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $this->maskValue($v);
            }

            return $out;
        }

        return $value;
    }

    protected function maskString(string $s): string
    {
        // emails
        $s = preg_replace('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', '[REDACTED_EMAIL]', $s);

        // URLs
        $s = preg_replace('/https?:\/\/[^\s]+/i', '[REDACTED_URL]', $s);
        $s = preg_replace('/www\.[^\s]+/i', '[REDACTED_URL]', $s);

        // simple phone numbers (digits, spaces, +, -, parentheses)
        $s = preg_replace('/\+?\d[\d\s\-\(\)]{5,}\d/', '[REDACTED_PHONE]', $s);

        return $s;
    }

    protected function safeModelCandidates(ResumeAgent $agent): array
    {
        try {
            return $agent->textModelCandidates();
        } catch (Throwable $exception) {
            Log::warning('Could not resolve chat model candidates for logging.', [
                'provider' => (string) config('ai.default'),
                'exception_class' => $exception::class,
                'exception_code' => $exception->getCode(),
                'exception_message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    protected function truncateForLog(string $text, int $limit = 250): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit).'...';
    }
}
