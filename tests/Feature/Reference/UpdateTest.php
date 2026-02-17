<?php

namespace Tests\Feature\Reference;

use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_update_ok()
    {
        $user = User::factory()->create();
        $reference = Reference::factory()->create();

        $payload = Reference::factory()->make()->toArray();
        unset($payload['id']);

        $url = '/api/reference/'.$reference->id;
        $response = $this->actingAs($user)->patchJson($url, $payload);
        $response->assertOk();

        $row = $payload;
        unset($row['basics']);

        $this->assertDatabaseHas('references', $row);
    }

    public function test_reference_update_unauthenticated()
    {
        $reference = Reference::factory()->create();
        $payload = Reference::factory()->make()->toArray();

        $url = '/api/reference/'.$reference->id;
        $response = $this->patchJson($url, $payload);
        $response->assertUnauthorized();
    }
}
