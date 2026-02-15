<?php

namespace Tests\Feature\Basic;

use App\Models\Basic;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Basic::factory()->count($max)->create();
        $basic = Basic::first();
        $location = $basic->location;
        $profile = $basic->profiles->first();

        $url = "/api/basic";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has($max)
            ->first(fn (AssertableJson $json) =>
                $json->has('id')
                ->where('name', $basic->name)
                ->where('label', $basic->label)
                ->where('email', $basic->email)
                ->where('phone', $basic->phone)
                ->where('url', $basic->url)
                ->where('summary', $basic->summary)
            ->has('location', fn (AssertableJson $json) =>
                $json->where('address', $location->address)
                ->where('postalCode', $location->postalCode)
                ->where('city', $location->city)
                ->where('countryCode', $location->countryCode))
            ->has('profiles', 1, fn (AssertableJson $json) =>
                $json->where('network', $profile->network)
                ->where('username', $profile->username)
                ->where('url', $profile->url)
            )
            ->etc())
        );
    }
}
