<?php

namespace App\Services\Chat;

use App\Ai\Agents\ResumeAgent;
use App\Models\AiRequest;
use App\Models\Basic;

class ChatService
{
    public function __construct()
    {
    }

    public function reply(string $message, ?string $sessionId = null, ?array $metadata = null): array
    {
        $basic = Basic::query()->first();

        $contextParts = [];
        if ($basic) {
            if (!empty($basic->summary)) {
                $contextParts[] = "Summary: " . $basic->summary;
            }
            if (!empty($basic->name)) {
                $contextParts[] = "Name: " . $basic->name;
            }
        }

        $context = implode("\n", $contextParts);

        // Build agent with default instructions and include basic context as system prompt
        $agentInstructions = "You are a helpful assistant that answers questions about a user's resume. Use the provided resume context when relevant and be concise.";

        $agent = new ResumeAgent($agentInstructions, []);

        // Pre-check: allow only CV-related queries. If not allowed, return a polite refusal and log the attempt.
        $isAllowed = true;
        try {
            if (method_exists($agent, 'allowsQuery')) {
                $isAllowed = $agent->allowsQuery($message);
            }
        } catch (\Throwable) {
            $isAllowed = true;
        }

        if (! $isAllowed) {
            $refusal = 'Lo siento, sólo puedo responder preguntas relacionadas con el CV.';

            // Log the blocked attempt (masked)
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

        // Assemble user prompt including context
        $userPrompt = ($context ? ($context . "\n\n") : '') . $message;

        $response = $agent->prompt($userPrompt);

        $replyText = (string) $response;

        // include usage in metadata if available
        $usage = null;
        try {
            $usageObj = $response->usage ?? null;
            if ($usageObj) {
                $usage = [
                    'prompt_tokens' => $usageObj->prompt_tokens ?? null,
                    'completion_tokens' => $usageObj->completion_tokens ?? null,
                    'total_tokens' => $usageObj->total_tokens ?? null,
                ];
            }
        } catch (\Throwable) {
            $usage = null;
        }

        // Mask sensitive data before persisting logs
        $maskedPrompt = $this->maskValue($context);
        $maskedMessage = $this->maskValue($message);
        $maskedReply = $this->maskValue($replyText);
        $maskedMetadata = $this->maskValue(array_merge($metadata ?? [], ['usage' => $usage]));

        AiRequest::create([
            'session_id' => $sessionId,
            'provider' => config('ai.default'),
            'prompt' => $maskedPrompt,
            'message' => $maskedMessage,
            'reply' => $maskedReply,
            'metadata' => $maskedMetadata,
        ]);

        return [
            'reply' => $replyText,
            'sources' => [],
            'session_id' => $sessionId,
        ];
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
}
