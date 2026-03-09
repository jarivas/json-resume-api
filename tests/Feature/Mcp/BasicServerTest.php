<?php

namespace Tests\Feature\Mcp;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Basic;
use App\Models\Work;
use App\Models\Volunteer;
use App\Models\Education;
use App\Models\Award;
use App\Models\Certificate;
use App\Models\Publication;
use App\Models\Skill;
use App\Models\Language;
use App\Models\Interest;
use App\Models\Reference;
use App\Models\Project;

class BasicServerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_relations_and_meta()
    {
        $basic = Basic::factory()->create();

        Work::factory()->count(2)->create(['basic_id' => $basic->id]);
        Volunteer::factory()->count(1)->create(['basic_id' => $basic->id]);
        Education::factory()->count(1)->create(['basic_id' => $basic->id]);
        Award::factory()->count(1)->create(['basic_id' => $basic->id]);
        Certificate::factory()->count(1)->create(['basic_id' => $basic->id]);
        Publication::factory()->count(1)->create(['basic_id' => $basic->id]);
        Skill::factory()->count(1)->create(['basic_id' => $basic->id]);
        Language::factory()->count(1)->create(['basic_id' => $basic->id]);
        Interest::factory()->count(1)->create(['basic_id' => $basic->id]);
        Reference::factory()->count(1)->create(['basic_id' => $basic->id]);
        Project::factory()->count(1)->create(['basic_id' => $basic->id]);

        $ref = new \ReflectionClass(\App\Mcp\Servers\BasicServer::class);
        $srv = $ref->newInstanceWithoutConstructor();

        $result = $srv->index(['per_page' => 10, 'page' => 1]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);

        $this->assertCount(1, $result['data']);

        $item = $result['data'][0];

        $this->assertArrayHasKey('works', $item);
        $this->assertCount(2, $item['works']);
        $this->assertArrayHasKey('volunteers', $item);
        $this->assertArrayHasKey('educations', $item);
        $this->assertArrayHasKey('awards', $item);
        $this->assertArrayHasKey('certificates', $item);
        $this->assertArrayHasKey('publications', $item);
        $this->assertArrayHasKey('skills', $item);
        $this->assertArrayHasKey('languages', $item);
        $this->assertArrayHasKey('interests', $item);
        $this->assertArrayHasKey('references', $item);
        $this->assertArrayHasKey('projects', $item);

        $this->assertEquals(1, $result['meta']['current_page']);
        $this->assertEquals(10, $result['meta']['per_page']);
    }

    public function test_show_returns_item_with_relations()
    {
        $basic = Basic::factory()->create();

        Work::factory()->count(1)->create(['basic_id' => $basic->id]);
        Skill::factory()->count(1)->create(['basic_id' => $basic->id]);

        $ref = new \ReflectionClass(\App\Mcp\Servers\BasicServer::class);
        $srv = $ref->newInstanceWithoutConstructor();

        $result = $srv->show($basic->id);

        $this->assertArrayHasKey('works', $result);
        $this->assertArrayHasKey('skills', $result);
        $this->assertCount(1, $result['works']);
        $this->assertCount(1, $result['skills']);
    }
}
