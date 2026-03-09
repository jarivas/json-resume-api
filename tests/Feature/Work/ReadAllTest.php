<?php

namespace Tests\Feature\Work;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $max = 5;

        Work::factory()->count($max)->basic($basic->id)->create();
        $work = Work::first();

        $url = '/api/work';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $work->name)
                ->where('basic_id', $basic->id)
                ->where('position', $work->position)
                ->where('url', $work->url)
                ->where('startDate', $work->startDate->format('Y-m-d'))
                ->where('endDate', $work->endDate->format('Y-m-d'))
                ->where('summary', $work->summary)
                ->has('highlights')
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
