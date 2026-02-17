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
        $data = Volunteer::factory()->make()->toArray();
        if (empty($data['highlights'])) {
            $data['highlights'] = ['Highlight example'];
        }
        $data['basics'] = [$basic->id];

        $url = '/api/volunteer';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('organization', $data['organization'])
            ->etc());

        $this->assertDatabaseHas('volunteers', [
            'id' => $response->json('id'),
            'organization' => $data['organization'],
        ]);

        $this->assertDatabaseHas('basic_volunteers', [
            'volunteer_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_volunteer_create_no_organization()
    {
        $user = User::factory()->create();
        $data = Volunteer::factory()->make(['organization' => null])->toArray();
        $url = '/api/volunteer';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_volunteer_create_unauthenticated()
    {
        $data = Volunteer::factory()->make()->toArray();
        $url = '/api/volunteer';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
