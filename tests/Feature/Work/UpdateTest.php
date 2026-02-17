<?php

namespace Tests\Feature\Work;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_update_ok()
    {
        $user = User::factory()->create();
        $work = Work::factory()->create();

        $payload = Work::factory()->make()->toArray();
        unset($payload['id']);

        $url = '/api/work/'.$work->id;
        $response = $this->actingAs($user)->patchJson($url, $payload);
        $response->assertOk();

        $row = $payload;
        unset($row['basics']);
        unset($row['highlights']);

        $this->assertDatabaseHas('works', $row);
    }

    public function test_work_update_unauthenticated()
    {
        $work = Work::factory()->create();
        $payload = Work::factory()->make()->toArray();

        $url = '/api/work/'.$work->id;
        $response = $this->patchJson($url, $payload);
        $response->assertUnauthorized();
    }
}
