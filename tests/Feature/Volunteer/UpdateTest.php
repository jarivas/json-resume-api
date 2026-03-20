<?php

namespace Tests\Feature\Volunteer;

use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_volunteer_update_ok()
    {
        $user = User::factory()->create();
        $volunteer = Volunteer::factory()->create();
        $data = Volunteer::factory()->make()->toArray();

        $url = '/api/volunteer/'.$volunteer->id;
        $response = $this->actingAs($user)->patchJson($url, $data);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('organization', $data['organization'])
            ->where('position', $data['position'])
            ->where('url', $data['url'])
            ->where('startDate', $data['startDate'])
            ->where('endDate', $data['endDate'])
            ->where('summary', $data['summary'])
            ->has('highlights')
            ->etc());

        unset($data['highlights']);

        $dbData = array_merge(['id' => $volunteer->id], $data);
        $this->assertDatabaseHas('volunteers', $dbData);
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
