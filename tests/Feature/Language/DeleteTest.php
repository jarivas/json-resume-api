<?php

namespace Tests\Feature\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_delete_ok()
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();

        $url = "/api/language/{$language->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('languages', [
            'id' => $language->id,
        ]);
    }

    public function test_language_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/language/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_language_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/language/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_language_delete_unauthenticated()
    {
        $language = Language::factory()->create();

        $url = "/api/language/{$language->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
