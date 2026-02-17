<?php

namespace Tests\Feature\Certificate;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_delete_ok()
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();

        $url = "/api/certificate/{$certificate->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('certificates', [
            'id' => $certificate->id,
        ]);
    }

    public function test_certificate_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/certificate/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_certificate_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/certificate/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_certificate_delete_unauthenticated()
    {
        $certificate = Certificate::factory()->create();

        $url = "/api/certificate/{$certificate->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
