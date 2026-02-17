<?php

namespace Tests\Feature\Publication;

use App\Models\Basic;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Publication::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/publication';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('publisher', $data['publisher'])
            ->where('summary', $data['summary'])
            ->etc());

        $this->assertDatabaseHas('publications', [
            'id' => $response->json('id'),
            'name' => $data['name'],
            'publisher' => $data['publisher'],
            'summary' => $data['summary'],
        ]);

        $this->assertDatabaseHas('basic_publications', [
            'publication_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_publication_create_no_name()
    {
        $user = User::factory()->create();
        $data = Publication::factory()->make(['name' => null])->toArray();
        $url = '/api/publication';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_publication_create_unauthenticated()
    {
        $data = Publication::factory()->make()->toArray();
        $url = '/api/publication';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
