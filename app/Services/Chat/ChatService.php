<?php

namespace App\Services\Chat;

use App\Models\Basic;

class ChatService
{
    public function __construct(protected LlmClientInterface $llm)
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

        return $this->llm->generateReply($context, $message, $metadata, $sessionId);
    }
}
