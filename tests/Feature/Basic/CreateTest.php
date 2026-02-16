<?php

namespace Tests\Feature\Basic;

use App\Models\User;
use App\Models\Basic;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_create_ok()
    {
        $user = User::factory()->create();
        $data = Basic::factory()->make()->toArray();
        $location = $data["location"];
        $profile = $data["profiles"][0];

        $url = '/api/basic';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

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
    }

    public function test_basic_create_no_name_error()
    {
        $user = User::factory()->create();
        $data = Basic::factory()->make()->toArray();
        unset($data['name']);

        $url = '/api/basic';
        $response = $this->actingAs($user)->postJson($url, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_basic_create_no_label_error()
    {
        $user = User::factory()->create();
        $data = Basic::factory()->make()->toArray();
        unset($data['label']);
        $url = '/api/basic';
        $response = $this->actingAs($user)->postJson($url, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['label']);
    }

    public function test_basic_create_no_email_error()
    {
        $user = User::factory()->create();
        $data = Basic::factory()->make()->toArray();
        unset($data['email']);
        $url = '/api/basic';
        $response = $this->actingAs($user)->postJson($url, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_basic_create_no_phone_error()
    {
        $user = User::factory()->create();
        $data = Basic::factory()->make()->toArray();
        unset($data['phone']);
        $url = '/api/basic';
        $response = $this->actingAs($user)->postJson($url, $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_basic_create_unauthenticated()
    {
        $data = Basic::factory()->make()->toArray();
        $url = '/api/basic';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}