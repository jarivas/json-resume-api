<?php

namespace App\Services\Chat;

class StubLlmClient implements LlmClientInterface
{
    public function generateReply(string $context, string $message, ?array $metadata = null, ?string $sessionId = null): array
    {
        $reply = 'echo: '.$message;

        return [
            'reply' => $reply,
            'sources' => [],
            'session_id' => $sessionId ?? bin2hex(random_bytes(8)),
        ];
    }
}
