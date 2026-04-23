<?php

namespace Tests\Feature\Certificate;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_read_all_ok()
    {
        $user = User::factory()->create();
        $max = 5;

        Certificate::factory()->count($max)->create();
        $certificate = Certificate::first();

        $url = '/api/certificate';
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', $max));

        $response->assertJsonPath('data.0.name', $certificate->name);
        $response->assertJsonPath('data.0.issuer', $certificate->issuer);
        $response->assertJsonPath('data.0.url', $certificate->url);
    }

    public function test_certificate_read_all_unauthenticated()
    {
        $url = '/api/certificate';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
