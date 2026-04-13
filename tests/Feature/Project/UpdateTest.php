<?php

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_update_ok()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $data = Project::factory()->make()->toArray();

        $url = "/api/project/{$project->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->has('roles')
            ->where('description', $data['description'])
            ->where('url', $data['url'])
            ->has('highlights')
            ->etc());

        unset($data['roles']);
        unset($data['highlights']);
        $dbData = array_merge(['id' => $project->id], $data);
        $this->assertDatabaseHas('projects', $dbData);
    }

    public function test_project_unauthenticated()
    {
        $project = Project::factory()->create();
        $data = Project::factory()->make()->toArray();

        $url = "/api/project/{$project->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
