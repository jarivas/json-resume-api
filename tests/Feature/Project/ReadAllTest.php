<?php

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Project::factory()->count($max)->create();
        $project = Project::first();

        $url = '/api/project';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.name', $project->name);
        $response->assertJson(fn (AssertableJson $json) => $json->has('data.0.roles'));
        $response->assertJsonPath('data.0.description', $project->description);
        $response->assertJsonPath('data.0.url', $project->url);
    }

    public function test_project_read_all_unauthenticated()
    {
        $url = '/api/project';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
