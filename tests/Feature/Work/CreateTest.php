<?php

namespace Tests\Feature\Work;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $data = Work::factory()->make()->toArray();
        if (empty($data['highlights'])) {
            $data['highlights'] = ['Highlight example'];
        }
        $data['basics'] = [$basic->id];

        $url = '/api/work';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->etc());

        $this->assertDatabaseHas('works', [
            'id' => $response->json('id'),
            'name' => $data['name'],
        ]);

        $this->assertDatabaseHas('basic_works', [
            'work_id' => $response->json('id'),
            'basic_id' => $basic->id,
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
