<?php

namespace Tests\Feature\Interest;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_interest_delete_ok()
    {
        $user = User::factory()->create();
        $interest = Interest::factory()->create();

        $url = "/api/interest/{$interest->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('interests', [
            'id' => $interest->id,
        ]);
    }

    public function test_interest_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/interest/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_interest_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/interest/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_interest_delete_unauthenticated()
    {
        $interest = Interest::factory()->create();

        $url = "/api/interest/{$interest->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
