<?php

namespace Tests\Feature\Iso;

use Illuminate\Testing\Fluent\AssertableJson;
use Io238\ISOCountries\Models\Currency as Model;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    public function test_currency_read_all_ok()
    {
        $response = $this->getJson('/api/iso/currency');
        $response->assertOk();

        $first = Model::first();
        $count = Model::count();

        $response->assertJson(fn (AssertableJson $json) => $json->has($count)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('symbol', $first->symbol)
                ->etc())
        );
    }
}