<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_change_password_ok()
    {
        $user = User::factory()->create();
        $password = 'New_password123';
        $url = '/api/authentication/change-password';
        $response = $this->actingAs($user)->postJson($url, [
            'password' => $password,
        ]);
        $response->assertNoContent();

        $user->refresh();

        $this->assertTrue(Hash::check($password, $user->password));
    }
}