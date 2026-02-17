<?php

namespace Tests\Feature\Reference;

use App\Models\Reference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_delete_ok()
    {
        $user = User::factory()->create();
        $reference = Reference::factory()->create();

        $url = '/api/reference/'.$reference->id;
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('references', ['id' => $reference->id]);
    }

    public function test_reference_delete_unauthenticated()
    {
        $reference = Reference::factory()->create();

        $url = '/api/reference/'.$reference->id;
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
