<?php

namespace Tests\Feature\Basic;

use App\Models\User;
use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_delete_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();

        $url = "/api/basic/{$basic->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('basics', [
            'id' => $basic->id,
        ]);
    }

    public function test_basic_delete_fail()
    {
        $user = User::factory()->create();

        $url = "/api/basic/9999";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_basic_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/basic/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }
}