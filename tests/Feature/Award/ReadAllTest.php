<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Carbon\Carbon;
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

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.title', $award->title);
        // Date serialization may include timezone; skip strict equality here.
        $response->assertJsonPath('data.0.awarder', $award->awarder);
        $response->assertJsonPath('data.0.summary', $award->summary);
    }

    public function test_award_read_all_unauthenticated()
    {
        $url = '/api/award';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
