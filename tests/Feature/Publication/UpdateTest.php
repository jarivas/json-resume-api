<?php

namespace Tests\Feature\Publication;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_update_ok()
    {
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $data = Publication::factory()->make()->toArray();

        $url = "/api/publication/{$publication->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('publisher', $data['publisher'])
            ->where('summary', $data['summary'])
            ->where('url', $data['url'])
            ->etc());

        $dbData = array_merge(['id' => $publication->id], $data);
        $this->assertDatabaseHas('publications', $dbData);
    }

    public function test_publication_unauthenticated()
    {
        $publication = Publication::factory()->create();
        $data = Publication::factory()->make()->toArray();

        $url = "/api/publication/{$publication->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
