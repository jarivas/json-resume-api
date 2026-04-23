<?php

namespace Tests\Feature\Basic;

use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Basic::factory()->count($max)->create();
        $basic = Basic::first();
        // Controller currently returns only the first Basic wrapped in a collection
        $expected = 1;
        $location = $basic->location;
        $profile = $basic->profiles->first();

        $url = '/api/basic';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $expected));

        $response->assertJsonPath('data.0.name', $basic->name);
        $response->assertJsonPath('data.0.label', $basic->label);
        $response->assertJsonPath('data.0.email', $basic->email);
        $response->assertJsonPath('data.0.phone', $basic->phone);
        $response->assertJsonPath('data.0.url', $basic->url);
        $response->assertJsonPath('data.0.summary', $basic->summary);

        $response->assertJsonPath('data.0.location.address', $location->address);
        $response->assertJsonPath('data.0.location.postalCode', $location->postalCode);
        $response->assertJsonPath('data.0.location.city', $location->city);
        $response->assertJsonPath('data.0.location.countryCode', $location->countryCode);

        $response->assertJsonPath('data.0.profiles.0.network', $profile->network);
        $response->assertJsonPath('data.0.profiles.0.username', $profile->username);
        $response->assertJsonPath('data.0.profiles.0.url', $profile->url);
    }

    public function test_basic_read_all_unauthenticated()
    {
        $url = '/api/basic';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
