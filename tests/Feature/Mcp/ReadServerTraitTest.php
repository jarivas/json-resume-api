<?php

namespace Tests\Feature\Mcp;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadServerTraitTest extends TestCase
{
    use RefreshDatabase;

    protected function createDummyServer(string $model)
    {
        return new class($model)
        {
            public string $model;

            public function __construct(string $model)
            {
                $this->model = $model;
            }

            use \App\Mcp\Servers\Traits\ReadServerTrait;
        };
    }

    public function test_q_search_on_fillable_fields_returns_matching_records()
    {
        Work::factory()->create(['position' => 'Senior Engineer']);
        Work::factory()->create(['position' => 'Marketing Manager']);

        $srv = $this->createDummyServer(Work::class);

        $result = $srv->readIndex(['q' => 'Engineer', 'per_page' => 10, 'page' => 1]);

        $this->assertArrayHasKey('data', $result);
        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('Engineer', $result['data'][0]['position']);
    }

    public function test_field_operator_gte_filters_results()
    {
        Work::factory()->create(['startDate' => '2020-01-01']);
        Work::factory()->create(['startDate' => '2022-06-01']);

        $srv = $this->createDummyServer(Work::class);

        $result = $srv->readIndex(['startDate__gte' => '2021-01-01', 'per_page' => 10, 'page' => 1]);

        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('2022', $result['data'][0]['startDate']);
    }

    public function test_relation_filter_where_has_returns_related_records()
    {
        Work::factory()->create(['position' => 'Developer']);
        Work::factory()->create(['position' => 'Operations']);

        $srv = $this->createDummyServer(Work::class);

        $result = $srv->readIndex(['position__like' => 'Develop', 'per_page' => 10, 'page' => 1]);

        $this->assertCount(1, $result['data']);
        $this->assertStringContainsString('Develop', $result['data'][0]['position']);
    }
}
