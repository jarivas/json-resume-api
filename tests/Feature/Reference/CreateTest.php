<?php

namespace Tests\Feature\Reference;

use App\Models\Basic;
use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Reference::factory()->basic($basic->id)->make()->toArray();

        $url = '/api/reference';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('reference', $data['reference'])
            ->where('basic_id', $basic->id)
            ->etc());

        $this->assertDatabaseHas('references', [
            'id' => $response->json('id'),
            'name' => $data['name'],
            'reference' => $data['reference'],
            'basic_id' => $basic->id,
        ]);
    }

    public function test_reference_create_no_name()
    {
        $user = User::factory()->create();
        $data = Reference::factory()->make(['name' => null])->toArray();
        $url = '/api/reference';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_reference_create_unauthenticated()
    {
        $data = Reference::factory()->make()->toArray();
        $url = '/api/reference';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
