<?php

namespace App\Helpers\Http\Controllers\Authentication;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

trait Login
{
    protected function getToken(User $user): array
    {
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;
        $seconds = intval(config('sanctum.expiration'));
        $expiresAt = now()->addSeconds($seconds);

        return [
            'token' => $token,
            'expiresAt' => $expiresAt->format('Y-m-d H:i:s')
        ];
    }

    protected function responseOk(): JsonResponse
    {
        $user = Auth::user();
        $data = $this->getToken($user);

        return response()->json($data);
    }
}