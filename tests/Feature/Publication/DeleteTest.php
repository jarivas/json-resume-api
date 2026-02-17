<?php

namespace Tests\Feature\Publication;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_delete_ok()
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        $url = "/api/publication/{$publication->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('publications', [
            'id' => $publication->id,
        ]);
    }

    public function test_publication_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/publication/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_publication_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/publication/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_publication_delete_unauthenticated()
    {
        $publication = Publication::factory()->create();

        $url = "/api/publication/{$publication->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
