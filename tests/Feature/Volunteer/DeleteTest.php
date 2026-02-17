<?php

namespace Tests\Feature\Volunteer;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_delete_ok()
    {
        $user = User::factory()->create();
        $volunteer = Volunteer::factory()->create();

        $url = '/api/volunteer/'.$volunteer->id;
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('volunteers', ['id' => $volunteer->id]);
    }

    public function test_volunteer_delete_unauthenticated()
    {
        $volunteer = Volunteer::factory()->create();

        $url = '/api/volunteer/'.$volunteer->id;
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
