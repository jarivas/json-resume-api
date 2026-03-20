<?php

namespace App\Http\Controllers\Chat;

use App\Http\Requests\Chat\Chat as Request;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;

class Chat
{
    public function __construct(protected ChatService $chatService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->chatService->reply($data['message'], $data['session_id'] ?? null, $data['metadata'] ?? null);

        return response()->json($result);
    }
}
