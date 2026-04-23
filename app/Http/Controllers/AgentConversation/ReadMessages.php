<?php

namespace App\Http\Controllers\AgentConversation;

use App\Models\AgentConversation;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReadMessages
{
    public function __invoke(AgentConversation $conversation, int $page = 1)
    {
        if ($conversation->user_id !== auth()->id()) {
            throw new HttpException(403, 'Forbidden');
        }

        $messages = $conversation->messages()->latest()->paginate(15, ['*'], 'page', $page);

        return response()->json($messages);
    }
}
