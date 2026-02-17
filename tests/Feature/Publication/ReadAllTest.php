<?php

namespace Tests\Feature\Publication;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Publication::factory()->count($max)->create();
        $publication = Publication::first();

        $url = '/api/publication';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $publication->name)
                ->where('publisher', $publication->publisher)
                ->etc())
        );
    }

    public function test_publication_read_all_unauthenticated()
    {
        $url = '/api/publication';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
