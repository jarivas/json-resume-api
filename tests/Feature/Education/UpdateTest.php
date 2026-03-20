<?php

namespace Tests\Feature\Education;

use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_update_ok()
    {
        $user = User::factory()->create();
        $education = Education::factory()->create();
        $data = Education::factory()->make()->toArray();

        $url = "/api/education/{$education->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('institution', $data['institution'])
            ->where('url', $data['url'])
            ->where('area', $data['area'])
            ->where('studyType', $data['studyType'])
            ->where('score', $data['score'])
            ->where('summary', $data['summary'])
            ->has('courses')
            ->etc());

        unset($data['courses']);
        $dbData = array_merge(['id' => $education->id], $data);
        $this->assertDatabaseHas('educations', $dbData);
    }

    public function test_education_update_unauthenticated()
    {
        $education = Education::factory()->create();
        $data = Education::factory()->make()->toArray();

        $url = "/api/education/{$education->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
