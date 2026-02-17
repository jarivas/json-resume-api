<?php

namespace Tests\Feature\Language;

use App\Models\Basic;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_update_ok()
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();
        $basic = Basic::factory()->create();
        $data = Language::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = "/api/language/{$language->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('language', $data['language'])
            ->where('fluency', $data['fluency'])
            ->etc());

        $tmp = $data;
        unset($tmp['basics']);
        $dbData = array_merge(['id' => $language->id], $tmp);
        $this->assertDatabaseHas('languages', $dbData);

        $this->assertDatabaseHas('basic_languages', [
            'language_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_language_unauthenticated()
    {
        $language = Language::factory()->create();
        $data = Language::factory()->make()->toArray();

        $url = "/api/language/{$language->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
