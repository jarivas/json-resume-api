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

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $work->name)
                ->etc())
        );
    }

    public function test_work_read_all_unauthenticated()
    {
        $url = '/api/work';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
