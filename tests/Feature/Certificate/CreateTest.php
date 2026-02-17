<?php

namespace Tests\Feature\Certificate;

use App\Models\Basic;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_create_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $data = Certificate::factory()->make()->toArray();
        $data['basics'] = [$basic->id];

        $url = '/api/certificate';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertCreated();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
            ->where('name', $data['name'])
            ->where('date', $data['date'])
            ->where('issuer', $data['issuer'])
            ->where('url', $data['url'])
            ->etc());

        $this->assertDatabaseHas('certificates', [
            'id' => $response->json('id'),
            'name' => $data['name'],
            'date' => $data['date'],
            'issuer' => $data['issuer'],
            'url' => $data['url'],
        ]);

        $this->assertDatabaseHas('basic_certificates', [
            'certificate_id' => $response->json('id'),
            'basic_id' => $basic->id,
        ]);
    }

    public function test_certificate_create_no_name()
    {
        $user = User::factory()->create();
        $data = Certificate::factory()->make(['name' => null])->toArray();
        $url = '/api/certificate';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_certificate_create_no_date()
    {
        $user = User::factory()->create();
        $data = Certificate::factory()->make(['date' => null])->toArray();
        $url = '/api/certificate';
        $response = $this->actingAs($user)->postJson($url, $data);
        $response->assertUnprocessable();
    }

    public function test_certificate_create_unauthenticated()
    {
        $data = Certificate::factory()->make()->toArray();
        $url = '/api/certificate';
        $response = $this->postJson($url, $data);
        $response->assertUnauthorized();
    }
}
