<?php

namespace Tests\Feature\Interest;

use App\Models\Basic;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_interest_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Interest::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/interest';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->has('keywords')
            ->where('basic_id', $basic->id)
            ->etc());

        $this->assertDatabaseHas('interests', [
            'id' => $response->json('id'),
            'name' => $data['name'],
            'basic_id' => $basic->id,
        ]);
    }

    public function test_interest_create_no_name()
    {
        $user = User::factory()->create();
        $data = Interest::factory()->make(['name' => null])->toArray();
        $url = '/api/interest';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_interest_create_unauthenticated()
    {
        $data = Interest::factory()->make()->toArray();
        $url = '/api/interest';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
