<?php

namespace Tests\Feature\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Language::factory()->count($max)->create();
        $language = Language::first();

        $url = '/api/language';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('language', $language->language)
                ->where('fluency', $language->fluency)
                ->etc())
        );
    }

    public function test_language_read_all_unauthenticated()
    {
        $url = '/api/language';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
