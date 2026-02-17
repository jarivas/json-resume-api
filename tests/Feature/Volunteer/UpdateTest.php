<?php

namespace Tests\Feature\Volunteer;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_update_ok()
    {
        $user = User::factory()->create();
        $volunteer = Volunteer::factory()->create();

        $payload = Volunteer::factory()->make()->toArray();
        unset($payload['id']);

        $url = '/api/volunteer/'.$volunteer->id;
        $response = $this->actingAs($user)->patchJson($url, $payload);
        $response->assertOk();

        $row = $payload;
        unset($row['basics']);
        unset($row['highlights']);

        $this->assertDatabaseHas('volunteers', $row);
    }

    public function test_volunteer_update_unauthenticated()
    {
        $volunteer = Volunteer::factory()->create();
        $payload = Volunteer::factory()->make()->toArray();

        $url = '/api/volunteer/'.$volunteer->id;
        $response = $this->patchJson($url, $payload);
        $response->assertUnauthorized();
    }
}
