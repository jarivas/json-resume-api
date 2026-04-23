<?php

namespace Tests\Feature\Work;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Work::factory()->count($max)->create();
        $work = Work::first();

        $url = '/api/work';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.name', $work->name);
        $response->assertJsonPath('data.0.position', $work->position);
        $response->assertJsonPath('data.0.url', $work->url);
        $response->assertJson(fn (AssertableJson $json) => $json->has('data.0.highlights'));
    }

    public function test_work_read_all_unauthenticated()
    {
        $url = '/api/work';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
