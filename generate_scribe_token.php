<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Read env from .env file if not present in environment
$envPath = __DIR__ . '/.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\n\r\0\x0B\"");
        }
    }
}

$email = $env['USER_EMAIL'] ?? getenv('USER_EMAIL');
$password = $env['USER_PASSWORD'] ?? getenv('USER_PASSWORD');

if (! $email || ! $password) {
    echo "Missing USER_EMAIL or USER_PASSWORD environment variables\n";
    exit(1);
}

$user = User::where('email', $email)->first();
if (! $user) {
    $user = User::create([
        'name' => 'Scribe Doc User',
        'email' => $email,
        'password' => bcrypt($password),
    ]);
}

$user->tokens()->delete();
$token = $user->createToken('scribe')->plainTextToken;

echo $token;
