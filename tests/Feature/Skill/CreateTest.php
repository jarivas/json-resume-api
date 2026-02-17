<?php

namespace Tests\Feature\Skill;

use App\Models\Basic;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Skill::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/skill';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->etc());

        $this->assertDatabaseHas('skills', [
            'id' => $response->json('id'),
            'name' => $data['name'],
        ]);

        $this->assertDatabaseHas('basic_skills', [
            'skill_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_skill_create_no_name()
    {
        $user = User::factory()->create();
        $data = Skill::factory()->make(['name' => null])->toArray();
        $url = '/api/skill';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_skill_create_unauthenticated()
    {
        $data = Skill::factory()->make()->toArray();
        $url = '/api/skill';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
