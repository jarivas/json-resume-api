<?php

namespace Tests\Feature\Education;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Education::factory()->count($max)->create();
        $education = Education::first();

        $url = '/api/education';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.institution', $education->institution);
        $response->assertJsonPath('data.0.area', $education->area);
        $response->assertJsonPath('data.0.studyType', $education->studyType);
    }

    public function test_education_read_all_unauthenticated()
    {
        $url = '/api/education';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
