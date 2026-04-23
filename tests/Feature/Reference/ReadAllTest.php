<?php

namespace Tests\Feature\Reference;

use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Reference::factory()->count($max)->create();
        $reference = Reference::first();

        $url = '/api/reference';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.name', $reference->name);
        $response->assertJsonPath('data.0.reference', $reference->reference);
    }

    public function test_reference_read_all_unauthenticated()
    {
        $url = '/api/reference';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
