<?php

namespace Tests\Feature\Language;

use App\Models\Basic;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Language::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/language';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('language', $data['language'])
            ->where('fluency', $data['fluency'])
            ->where('basic_id', $basic->id)
            ->etc());

        $this->assertDatabaseHas('languages', [
            'id' => $response->json('id'),
            'language' => $data['language'],
            'fluency' => $data['fluency'],
            'basic_id' => $basic->id,
        ]);
    }

    public function test_language_create_no_language()
    {
        $user = User::factory()->create();
        $data = Language::factory()->make(['language' => null])->toArray();
        $url = '/api/language';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_language_create_unauthenticated()
    {
        $data = Language::factory()->make()->toArray();
        $url = '/api/language';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
