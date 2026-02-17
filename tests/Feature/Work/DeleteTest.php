<?php

namespace Tests\Feature\Work;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_delete_ok()
    {
        $user = User::factory()->create();
        $work = Work::factory()->create();

        $url = '/api/work/'.$work->id;
        $response = $this->actingAs($user)->deleteJson($url);
        $response->assertNoContent();

        $this->assertDatabaseMissing('works', ['id' => $work->id]);
    }

    public function test_work_delete_unauthenticated()
    {
        $work = Work::factory()->create();

        $url = '/api/work/'.$work->id;
        $response = $this->deleteJson($url);
        $response->assertUnauthorized();
    }
}
