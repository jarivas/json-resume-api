<?php

namespace Tests\Feature\Mcp\Http;

use Tests\TestCase;
use App\Models\User;

class AuthenticationHttpTest extends TestCase
{
    public function test_login_endpoint_returns_200_with_valid_credentials()
    {
        $password = 'secret123';
        $user = User::factory()->create(["password" => bcrypt($password)]);

        $response = $this->post('/mcp/auth/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200);
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->post('/mcp/auth/logout');

        $response->assertStatus(302);
    }
}
