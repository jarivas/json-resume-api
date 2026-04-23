<?php

namespace Tests\Feature\Basic;

use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_read_one_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $location = $basic->location;
        $profile = $basic->profiles->first();

        $url = "/api/basic/{$basic->id}";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data') );

        $response->assertJsonPath('data.id', $basic->id);
        $response->assertJsonPath('data.name', $basic->name);
        $response->assertJsonPath('data.label', $basic->label);
        $response->assertJsonPath('data.email', $basic->email);
        $response->assertJsonPath('data.phone', $basic->phone);
        $response->assertJsonPath('data.url', $basic->url);
        $response->assertJsonPath('data.summary', $basic->summary);

        $response->assertJsonPath('data.location.address', $location->address);
        $response->assertJsonPath('data.location.postalCode', $location->postalCode);
        $response->assertJsonPath('data.location.city', $location->city);
        $response->assertJsonPath('data.location.countryCode', $location->countryCode);

        $response->assertJsonPath('data.profiles.0.network', $profile->network);
        $response->assertJsonPath('data.profiles.0.username', $profile->username);
        $response->assertJsonPath('data.profiles.0.url', $profile->url);
    }

    public function test_basic_read_one_not_found()
    {
        $user = User::factory()->create();
        $url = '/api/basic/999';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertNotFound();
    }

    public function test_basic_read_one_not_found_ulid()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';
        $url = "/api/basic/{$ulid}";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertNotFound();
    }

    public function test_basic_read_one_unauthenticated()
    {
        $basic = Basic::factory()->create();
        $url = "/api/basic/{$basic->id}";
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
