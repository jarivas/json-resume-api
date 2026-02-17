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

        $response->assertJson(fn (AssertableJson $json) => $json->has($max)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $certificate->name)
                ->where('date', $certificate->date->format('Y-m-d'))
                ->where('issuer', $certificate->issuer)
                ->where('url', $certificate->url)
                ->etc())
        );
    }

    public function test_certificate_read_all_unauthenticated()
    {
        $url = '/api/certificate';
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}
