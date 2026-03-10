<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;


class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_token_ok()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->post('/api/authentication/refresh-token');

        $response->assertStatus(200);
    }
}