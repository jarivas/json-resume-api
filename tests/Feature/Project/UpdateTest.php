<?php

namespace Tests\Feature\Project;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $data = Project::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = "/api/project/{$project->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('description', $data['description'])
            ->where('url', $data['url'])
            ->etc());

        $tmp = $data;
        unset($tmp['basics']);
        if (array_key_exists('highlights', $tmp)) {
            unset($tmp['highlights']);
        }
        $dbData = array_merge(['id' => $project->id], $tmp);
        $this->assertDatabaseHas('projects', $dbData);

        $this->assertDatabaseHas('basic_projects', [
            'project_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
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
