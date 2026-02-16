<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Award::factory()->count($max)->create();
        $award = Award::first();

        $url = '/api/award';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
            ->where('title', $award->title)
            ->where('date', $award->date->format('Y-m-d'))
            ->where('awarder', $award->awarder)
            ->where('summary', $award->summary)
                ->etc())
        );
    }

    public function test_award_read_all_unauthenticated()
    {
        $url = '/api/award';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
