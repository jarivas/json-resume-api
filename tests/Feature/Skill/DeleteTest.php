<?php

namespace Tests\Feature\Skill;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_delete_ok()
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();

        $url = '/api/skill/'.$skill->id;
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_skill_delete_unauthenticated()
    {
        $skill = Skill::factory()->create();

        $url = '/api/skill/'.$skill->id;
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
