<?php

namespace Tests\Feature\Reference;

use App\Models\Basic;
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
        $basic = Basic::factory()->create();
        $max = 5;

        Reference::factory($max)->basic($basic->id)->create();
        $reference = Reference::first();

        $url = '/api/reference';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $reference->name)
                ->where('reference', $reference->reference)
                ->where('basic_id', $basic->id)
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
