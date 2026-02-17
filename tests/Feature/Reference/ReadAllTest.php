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

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $reference->name)
                ->etc())
        );
    }

    public function test_reference_read_all_unauthenticated()
    {
        $url = '/api/reference';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
