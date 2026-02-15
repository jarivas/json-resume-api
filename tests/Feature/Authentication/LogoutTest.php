<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_logout_ok()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $url = '/api/authentication/logout';
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson($url);

        $response->assertNoContent();

        $this->assertDatabaseEmpty('personal_access_tokens');
    }
}