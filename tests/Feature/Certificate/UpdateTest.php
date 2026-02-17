<?php

namespace Tests\Feature\Certificate;

use App\Models\Basic;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_update_ok()
    {
        $user = User::factory()->create();
        $certificate = Certificate::factory()->create();
        $basic = Basic::factory()->create();
        $data = Certificate::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = "/api/certificate/{$certificate->id}";
        $response = $this->actingAs($user)->patchJson($url, $data);

        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('date', $data['date'])
            ->where('issuer', $data['issuer'])
            ->where('url', $data['url'])
            ->etc());

        unset($data['basics']);
        $data = array_merge(['id' => $certificate->id], $data);
        $this->assertDatabaseHas('certificates', $data);

        $this->assertDatabaseHas('basic_certificates', [
            'certificate_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_certificate_unauthenticated()
    {
        $certificate = Certificate::factory()->create();
        $data = Certificate::factory()->make()->toArray();

        $url = "/api/certificate/{$certificate->id}";
        $response = $this->patchJson($url, $data);

        $response->assertUnauthorized();
    }
}
