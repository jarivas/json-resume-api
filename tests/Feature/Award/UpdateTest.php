<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_update_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $award = Award::factory()->create();
        $data = Award::factory()->basic($basic->id)->make()->toArray();

        $url = "/api/award/{$award->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('title', $data['title'])
            ->where('date', $data['date'])
            ->where('awarder', $data['awarder'])
            ->where('summary', $data['summary'])
            ->where('basic_id', $basic->id)
            ->etc());

        $data = array_merge(['id' => $award->id], $data);
        $this->assertDatabaseHas('awards', $data);
    }

    public function test_award_unauthenticated()
    {
        $award = Award::factory()->create();
        $data = Award::factory()->make()->toArray();

        $url = "/api/award/{$award->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
