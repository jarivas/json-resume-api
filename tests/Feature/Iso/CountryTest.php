<?php

namespace Tests\Feature\Iso;

use Illuminate\Testing\Fluent\AssertableJson;
use Io238\ISOCountries\Models\Country as Model;
use Tests\TestCase;

class CountryTest extends TestCase
{
    public function test_country_read_all_ok()
    {
        $response = $this->getJson('/api/iso/country');
        $response->assertOk();

        $first = Model::first();
        $count = Model::count();

        $response->assertJson(fn (AssertableJson $json) => $json->has($count)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('native_name', $first->native_name)
                ->where('alpha3', $first->alpha3)
                ->etc())
        );
    }
}