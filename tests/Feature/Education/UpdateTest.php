<?php

namespace Tests\Feature\Education;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $data = Education::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = "/api/education/{$education->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('institution', $data['institution'])
            ->where('area', $data['area'])
            ->where('studyType', $data['studyType'])
            ->where('score', $data['score'])
            ->where('summary', $data['summary'])
            ->etc());

        $tmp = $data;
        unset($tmp['basics']);
        if (array_key_exists('courses', $tmp)) {
            unset($tmp['courses']);
        }
        $dbData = array_merge(['id' => $education->id], $tmp);
        $this->assertDatabaseHas('educations', $dbData);

        $this->assertDatabaseHas('basic_educations', [
            'education_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_education_unauthenticated()
    {
        $education = Education::factory()->create();
        $data = Education::factory()->make()->toArray();

        $url = "/api/education/{$education->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
