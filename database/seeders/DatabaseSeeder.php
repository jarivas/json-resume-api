<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = new User([
            'name' => 'Resume',
            'email' => env('USER_EMAIL'),
            'password' => env('USER_PASSWORD'),
        ]);

        $user->save();
    }
}
