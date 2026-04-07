<?php

namespace App\Services\Authentication;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthenticationService
{
    public function login(array $credentials): false|array
    {
        $email = $credentials['email'];

        $exists = User::where('email', $email)->exists();
        if (! $exists) {
            return false;
        }

        if (! Auth::attempt($credentials, false)) {
            return false;
        }

        $user = Auth::user();

        return $this->getToken($user);
    }

    protected function getToken(User $user): array
    {
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;
        $seconds = intval(config('sanctum.expiration'));
        $expiresAt = now()->addSeconds($seconds);

        return [
            'token' => $token,
            'expiresAt' => $expiresAt->format('Y-m-d H:i:s'),
        ];
    }

    public function logout(): void
    {
        $user = auth('sanctum')->user();

        if ($user instanceof User) {
            $user->tokens()->delete();
        }
    }

    public function refreshToken(): false|array
    {
        $user = auth('sanctum')->user();

        if (! $user instanceof User) {
            return false;
        }

        $user->tokens()->delete();

        return $this->getToken($user);
    }

    public function recovery(): bool
    {
        $user = User::first();

        if (! $user) {
            return false;
        }

        $data = $this->getToken($user);

        Mail::send('mail.recovery', $data, fn ($message) => $message->to($user->email)->subject('Recovery Instructions'));

        return true;
    }
}
