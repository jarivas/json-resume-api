<?php

namespace Tests\Feature\Award;

use App\Models\Award;
use App\Models\Basic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttachTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_attach_ok()
    {
        $user = User::factory()->create();
        $award = Award::factory()->create();
        $basic = Basic::factory()->create();
        $data = [
            'ids' => [$basic->id],
        ];

        $url = "/api/award/{$award->id}/attach";
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->ddBody();
        $response->assertNoContent();

        $this->assertDatabaseHas('basic_awards', [
            'basic_id' => $basic->id,
            'award_id' => $award->id,
        ]);
    }
}
