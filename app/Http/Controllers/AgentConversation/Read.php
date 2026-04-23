<?php

namespace App\Http\Controllers\AgentConversation;

use App\Models\AgentConversation;

class Read
{
    public function __invoke(int $page = 1)
    {
        $items = AgentConversation::where('user_id', auth()->id())
            ->latest('updated_at')
            ->paginate(15, ['*'], 'page', $page);

        return response()->json($items);
    }
}
