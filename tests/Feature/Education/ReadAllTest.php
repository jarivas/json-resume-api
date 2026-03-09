<?php

namespace Tests\Feature\Education;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $max = 5;

        Education::factory($max)->basic($basic->id)->create();
        $education = Education::first();

        $url = '/api/education';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('institution', $education->institution)
                ->where('area', $education->area)
                ->where('studyType', $education->studyType)
                ->where('basic_id', $education->basic_id)
                ->etc())
        );
    }

    public function test_education_read_all_unauthenticated()
    {
        $url = '/api/education';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
