<?php

namespace Tests\Feature\Project;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();

        Project::factory($max)->basic($basic->id)->create();
        $project = Project::first();

        $url = '/api/project';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
                ->first(fn (AssertableJson $json) => $json->has('id')
                    ->where('name', $project->name)
                    ->where('description', $project->description)
                    ->where('basic_id', $project->basic_id)
                    ->has('highlights')
                    ->etc())
        );
    }

    public function test_project_read_all_unauthenticated()
    {
        $url = '/api/project';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
