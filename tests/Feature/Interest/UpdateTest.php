<?php

namespace Tests\Feature\Interest;

use App\Models\Basic;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_interest_update_ok()
    {
        $user = User::factory()->create();
        $interest = Interest::factory()->create();
        $basic = Basic::factory()->create();
        $data = Interest::factory()->basic($basic->id)->make()->toArray();

        $url = "/api/interest/{$interest->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->has('keywords')
            ->etc());

        unset($data['keywords']);

        $dbData = array_merge(['id' => $interest->id], $data);
        $this->assertDatabaseHas('interests', $dbData);
    }

    public function test_interest_unauthenticated()
    {
        $interest = Interest::factory()->create();
        $data = Interest::factory()->make()->toArray();

        $url = "/api/interest/{$interest->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
