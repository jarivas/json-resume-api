<?php

namespace Tests\Feature\Interest;

use App\Models\Basic;
use App\Models\Interest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_interest_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;
        $basic = Basic::factory()->create();

        Interest::factory($max)->basic($basic->id)->create();
        $interest = Interest::first();

        $url = '/api/interest';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $interest->name)
                ->where('basic_id', $interest->basic_id)
                ->etc())
        );
    }

    public function test_interest_read_all_unauthenticated()
    {
        $url = '/api/interest';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
