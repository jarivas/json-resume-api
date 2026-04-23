<?php

namespace Tests\Feature\Volunteer;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Volunteer::factory()->count($max)->create();
        $volunteer = Volunteer::first();

        $url = '/api/volunteer';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.organization', $volunteer->organization);
        $response->assertJsonPath('data.0.position', $volunteer->position);
        $response->assertJsonPath('data.0.url', $volunteer->url);
        $response->assertJsonPath('data.0.startDate', $volunteer->startDate->format('Y-m-d'));
        $response->assertJsonPath('data.0.endDate', $volunteer->endDate->format('Y-m-d'));
        $response->assertJsonPath('data.0.summary', $volunteer->summary);
        $response->assertJson(fn (AssertableJson $json) => $json->has('data.0.highlights'));
    }

    public function test_volunteer_read_all_unauthenticated()
    {
        $url = '/api/volunteer';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
