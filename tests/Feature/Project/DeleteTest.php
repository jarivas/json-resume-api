<?php

namespace Tests\Feature\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_delete_ok()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $url = "/api/project/{$project->id}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_project_delete_fail()
    {
        $user = User::factory()->create();

        $url = '/api/project/9999';
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_project_delete_ulid_fail()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';

        $url = "/api/project/{$ulid}";
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNotFound();
    }

    public function test_project_delete_unauthenticated()
    {
        $project = Project::factory()->create();

        $url = "/api/project/{$project->id}";
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
