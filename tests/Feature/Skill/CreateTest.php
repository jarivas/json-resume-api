<?php

namespace Tests\Feature\Skill;

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
        $data = Skill::factory()->make()->toArray();

        $url = '/api/skill';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->has('keywords')
            ->etc());

        $this->assertDatabaseHas('skills', [
            'id' => $response->json('id'),
            'name' => $data['name'],
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
