<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Helpers\Http\Controllers\Authentication\Login as LoginHelper;

#[Name('Authentication Server')]
#[Version('0.0.1')]
#[Instructions('Server for authentication actions like login, logout and recovery.')]
class AuthenticationServer extends Server
{
    protected array $tools = [];
    protected array $resources = [];
    protected array $prompts = [];
    use LoginHelper;

    public function login(array $credentials): array
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string',
        ];

        Validator::make($credentials, $rules)->validate();

        $email = $credentials['email'];

        $exists = User::where('email', $email)->exists();
        if (! $exists) {
            return ['error' => ['field' => 'email', 'message' => 'The provided email does not exist.'], 'status' => 400];
        }

        if (! Auth::attempt($credentials, false)) {
            return ['error' => ['field' => 'password', 'message' => 'The provided password is incorrect.'], 'status' => 400];
        }

        $user = Auth::user();

        return $this->getToken($user);
    }

    public function logout(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $user->tokens()->delete();

        return true;
    }
}
