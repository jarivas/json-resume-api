<?php

namespace Tests\Feature\Skill;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_update_ok()
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();

        $payload = Skill::factory()->make()->toArray();
        unset($payload['id']);

        $url = '/api/skill/'.$skill->id;
        $response = $this->actingAs($user)->patchJson($url, $payload);
        $response->assertOk();

        $row = $payload;
        unset($row['basics']);
        unset($row['keywords']);

        $this->assertDatabaseHas('skills', $row);
    }

    public function test_skill_update_unauthenticated()
    {
        $skill = Skill::factory()->create();
        $payload = Skill::factory()->make()->toArray();

        $url = '/api/skill/'.$skill->id;
        $response = $this->patchJson($url, $payload);
        $response->assertUnauthorized();
    }
}
