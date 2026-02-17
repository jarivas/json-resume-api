<?php

namespace Tests\Feature\Iso;

use Illuminate\Testing\Fluent\AssertableJson;
use Io238\ISOCountries\Models\Language as Model;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    public function test_language_read_all_ok()
    {
        $response = $this->getJson('/api/iso/language');
        $response->assertOk();

        $first = Model::first();
        $count = Model::count();

        $response->assertJson(fn (AssertableJson $json) => $json->has($count)
            ->first(fn (AssertableJson $json) => $json->has('id')
                ->where('native_name', $first->native_name)
                ->where('iso639_2', $first->iso639_2)
                ->etc())
        );
    }
}