<?php

namespace Tests\Feature\Publication;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $data = Publication::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = "/api/publication/{$publication->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('publisher', $data['publisher'])
            ->where('summary', $data['summary'])
            ->etc());

        $tmp = $data;
        unset($tmp['basics']);
        $dbData = array_merge(['id' => $publication->id], $tmp);
        $this->assertDatabaseHas('publications', $dbData);

        $this->assertDatabaseHas('basic_publications', [
            'publication_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
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
