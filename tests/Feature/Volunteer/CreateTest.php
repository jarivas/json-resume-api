<?php

namespace Tests\Feature\Volunteer;

use App\Models\Basic;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Volunteer::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/volunteer';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('organization', $data['organization'])
            ->where('position', $data['position'])
            ->where('url', $data['url'])
            ->where('startDate', $data['startDate'])
            ->where('endDate', $data['endDate'])
            ->where('summary', $data['summary'])
            ->has('highlights')
            ->where('basic_id', $basic->id)
            ->etc());

        $this->assertDatabaseHas('volunteers', [
            'id' => $response->json('id'),
            'organization' => $data['organization'],
            'position' => $data['position'],
            'url' => $data['url'],
            'basic_id' => $basic->id,
        ]);
    }

    public function test_volunteer_create_no_organization()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Volunteer::factory()->basic($basic->id)->make(['organization' => null])->toArray();
        $url = '/api/volunteer';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_volunteer_create_unauthenticated()
    {
        $basic = Basic::factory()->create();
        $data = Volunteer::factory()->basic($basic->id)->make()->toArray();
        $url = '/api/volunteer';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
