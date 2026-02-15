<?php

namespace Tests\Feature\Basic;

use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_update_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Basic::factory()->make()->toArray();
        $location = $data["location"];
        $profile = $data["profiles"][0];

        $url = "/api/basic/{$basic->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('id')
            ->where('name', $data['name'])
            ->where('label', $data['label'])
            ->where('email', $data['email'])
            ->where('phone', $data['phone'])
            ->where('url', $data['url'])
            ->where('summary', $data['summary'])
            ->has('location', fn (AssertableJson $json) =>
                $json->where('address', $location['address'])
                ->where('postalCode', $location['postalCode'])
                ->where('city', $location['city'])
                ->where('countryCode', $location['countryCode']))
            ->has('profiles', 1, fn (AssertableJson $json) =>
                $json->where('network', $profile['network'])
                ->where('username', $profile['username'])
                ->where('url', $profile['url'])
            )
            ->etc());

        unset($data['location']);
        unset($data['profiles']);
        $this->assertDatabaseHas('basics', array_merge(['id' => $basic->id], $data));
    }
}
