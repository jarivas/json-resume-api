<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_delete_ok()
    {
        $user = User::factory()->create();
        $award = Award::factory()->create();

        $url = "/api/award/{$award->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('awards', [
            'id' => $award->id,
        ]);
    }

    public function test_award_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/award/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_award_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/award/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_award_delete_unauthenticated()
    {
        $award = Award::factory()->create();

        $url = "/api/award/{$award->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
