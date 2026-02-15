<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class RecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_recovery_ok()
    {
        $user = new User([
            'email' => env('USER_EMAIL'),
            'password' => env('USER_PASSWORD'),
        ]);

        $user->save();

        $url = '/api/authentication/recovery';
        $response = $this->postJson($url);

        $response->ddBody();

        $response->assertNoContent();
    }
}