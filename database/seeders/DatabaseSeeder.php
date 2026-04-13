<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = config('app.user_email');
        $password = config('app.user_password');

        if (empty($email) || empty($password)) {
            Log::error('DatabaseSeeder: USER_EMAIL or USER_PASSWORD not set');
            throw new \RuntimeException('USER_EMAIL and USER_PASSWORD must be set in environment or config.');
        }

        User::create([
            'email' => $email,
            'password' => $password,
        ]);
    }
}
