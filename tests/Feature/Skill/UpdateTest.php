<?php

namespace Tests\Feature\Skill;

use App\Models\Basic;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_update_ok()
    {
        $user = User::factory()->create();
        $skill = Skill::factory()->create();
        $basic = Basic::factory()->create();

        $data = Skill::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/skill/'.$skill->id;
        $response = $this->actingAs($user)->patchJson($url, $data);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('basic_id', $basic->id)
            ->has('keywords')
            ->etc());

        unset($data['keywords']);
        $dbData = array_merge(['id' => $skill->id], $data);
        $this->assertDatabaseHas('skills', $dbData);
    }

    public function test_skill_update_unauthenticated()
    {
        $skill = Skill::factory()->create();
        $basic = Basic::factory()->create();
        $payload = Skill::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/skill/'.$skill->id;
        $response = $this->patchJson($url, $payload);
        $response->assertUnauthorized();
    }
}
