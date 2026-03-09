<?php

namespace Tests\Feature\Skill;

use App\Models\Basic;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_read_all_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $max = 5;

        Skill::factory($max)->basic($basic->id)->create();
        $skill = Skill::first();

        $url = '/api/skill';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $skill->name)
                ->where('basic_id', $basic->id)
                ->has('keywords')
                ->etc())
        );
    }

    public function test_skill_read_all_unauthenticated()
    {
        $url = '/api/skill';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
