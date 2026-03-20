<?php

namespace Tests\Unit;

use App\Mcp\Servers\EducationServer;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Education;
use PHPUnit\Framework\TestCase;

class McpServersTraitTest extends TestCase
{
    public function test_education_server_uses_read_trait_and_has_model()
    {
        $uses = class_uses(EducationServer::class);

        $this->assertContains(ReadServerTrait::class, $uses);

        $reflection = new \ReflectionClass(EducationServer::class);
        $defaults = $reflection->getDefaultProperties();

        $this->assertArrayHasKey('model', $defaults);
        $this->assertEquals(Education::class, $defaults['model']);
    }
}
