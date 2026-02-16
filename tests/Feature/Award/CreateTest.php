<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Award::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/award';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('title', $data['title'])
            ->where('date', $data['date'])
            ->where('awarder', $data['awarder'])
            ->where('summary', $data['summary'])
            ->etc());

        $this->assertDatabaseHas('awards', [
            'id' => $response->json('id'),
            'title' => $data['title'],
            'date' => $data['date'],
            'awarder' => $data['awarder'],
            'summary' => $data['summary'],
        ]);

        $this->assertDatabaseHas('basic_awards', [
            'award_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_award_create_no_title()
    {
        $user = User::factory()->create();
        $data = Award::factory()->make(['title' => null])->toArray();
        $url = '/api/award';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_award_create_no_date()
    {
        $user = User::factory()->create();
        $data = Award::factory()->make(['date' => null])->toArray();
        $url = '/api/award';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_award_create_unauthenticated()
    {
        $data = Award::factory()->make()->toArray();
        $url = '/api/award';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
