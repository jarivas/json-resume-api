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

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('organization', $volunteer->organization)
                ->where('position', $volunteer->position)
                ->where('url', $volunteer->url)
                ->where('startDate', $volunteer->startDate->format('Y-m-d'))
                ->where('endDate', $volunteer->endDate->format('Y-m-d'))
                ->where('summary', $volunteer->summary)
                ->has('highlights')
                ->etc())
        );
    }

    public function test_volunteer_read_all_unauthenticated()
    {
        $url = '/api/volunteer';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
