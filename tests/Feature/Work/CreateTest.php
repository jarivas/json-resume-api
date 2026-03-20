<?php

namespace Tests\Feature\Work;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_create_ok()
    {
        $user = User::factory()->create();
        $data = Work::factory()->make()->toArray();

        $url = '/api/work';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('position', $data['position'])
            ->where('url', $data['url'])
            ->where('startDate', $data['startDate'])
            ->where('endDate', $data['endDate'])
            ->where('summary', $data['summary'])
            ->has('highlights')
            ->etc());

        $this->assertDatabaseHas('works', [
            'id' => $response->json('id'),
            'name' => $data['name'],
        ]);
    }

    public function test_work_create_no_name()
    {
        $user = User::factory()->create();
        $data = Work::factory()->make(['name' => null])->toArray();
        $url = '/api/work';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_work_create_unauthenticated()
    {
        $data = Work::factory()->make()->toArray();
        $url = '/api/work';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
