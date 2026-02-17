<?php

namespace Tests\Feature\Education;

use App\Models\Basic;
use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_education_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Education::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/education';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('institution', $data['institution'])
            ->where('area', $data['area'])
            ->where('studyType', $data['studyType'])
            ->where('score', $data['score'])
            ->where('summary', $data['summary'])
            ->etc());

        $this->assertDatabaseHas('educations', [
            'id' => $response->json('id'),
            'institution' => $data['institution'],
            'area' => $data['area'],
            'studyType' => $data['studyType'],
            'score' => $data['score'],
            'summary' => $data['summary'],
        ]);

        $this->assertDatabaseHas('basic_educations', [
            'education_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_education_create_no_institution()
    {
        $user = User::factory()->create();
        $data = Education::factory()->make(['institution' => null])->toArray();
        $url = '/api/education';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_education_create_unauthenticated()
    {
        $data = Education::factory()->make()->toArray();
        $url = '/api/education';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
