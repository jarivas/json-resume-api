<?php

namespace Tests\Feature\Education;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_delete_ok()
    {
        $user = User::factory()->create();
        $education = Education::factory()->create();

        $url = "/api/education/{$education->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('educations', [
            'id' => $education->id,
        ]);
    }

    public function test_education_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/education/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_education_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/education/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_education_delete_unauthenticated()
    {
        $education = Education::factory()->create();

        $url = "/api/education/{$education->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
