<?php

namespace Tests\Feature\Project;

use App\Models\Basic;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Project::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/project';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('description', $data['description'])
            ->where('url', $data['url'])
            ->etc());

        $this->assertDatabaseHas('projects', [
            'id' => $response->json('id'),
            'name' => $data['name'],
            'description' => $data['description'],
            'url' => $data['url'],
        ]);

        $this->assertDatabaseHas('basic_projects', [
            'project_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_project_create_no_name()
    {
        $user = User::factory()->create();
        $data = Project::factory()->make(['name' => null])->toArray();
        $url = '/api/project';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_project_create_unauthenticated()
    {
        $data = Project::factory()->make()->toArray();
        $url = '/api/project';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
