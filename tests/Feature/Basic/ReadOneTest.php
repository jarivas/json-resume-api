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

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $basic->name)
            ->where('label', $basic->label)
            ->where('email', $basic->email)
            ->where('phone', $basic->phone)
            ->where('url', $basic->url)
            ->where('summary', $basic->summary)
            ->has('location', fn (AssertableJson $json) => $json->where('address', $location->address)
                ->where('postalCode', $location->postalCode)
                ->where('city', $location->city)
                ->where('countryCode', $location->countryCode))
            ->has('profiles', 1, fn (AssertableJson $json) => $json->where('network', $profile->network)
                ->where('username', $profile->username)
                ->where('url', $profile->url)
            )
            ->etc()
        );
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
