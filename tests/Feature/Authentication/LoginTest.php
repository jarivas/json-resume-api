<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_login_ok()
    {
        $user = User::factory()->create();
        $url = '/api/authentication/login';
        $data = [
            'email' => $user->email,
            'password' => 'Password123',
        ];
        $response = $this->postJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('token')
                ->has('expiresAt'));
        
        $expiresAt = Carbon::parse($response->json('expiresAt'));

        $this->assertTrue($expiresAt->isFuture());
    }

    public function test_authentication_login_wrong_password()
    {
        $user = User::factory()->create();
        $url = '/api/authentication/login';
        $data = [
            'email' => $user->email,
            'password' => 'WrongPassword123',
        ];
        $response = $this->postJson($url, $data);

        $response->assertBadRequest();
    }

    public function test_authentication_login_wrong_email()
    {
        $url = '/api/authentication/login';
        $data = [
            'email' => 'wrong@example.com',
            'password' => 'WrongPassword123',
        ];
        $response = $this->postJson($url, $data);

        $response->assertBadRequest();
    }
}