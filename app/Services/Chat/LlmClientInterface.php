<?php

namespace App\Services\Chat;

interface LlmClientInterface
{
    /**
     * Generate a reply from the LLM given the assembled context and user message.
     * Returns an array with keys: reply (string), sources (array), session_id (string|null)
     */
    public function generateReply(string $context, string $message, ?array $metadata = null, ?string $sessionId = null): array;
}
