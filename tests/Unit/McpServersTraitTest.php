<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class McpServersTraitTest extends TestCase
{
    public function test_education_server_uses_read_trait_and_has_model()
    {
        $uses = class_uses(\App\Mcp\Servers\EducationServer::class);

        $this->assertContains(\App\Mcp\Servers\Traits\ReadServerTrait::class, $uses);

        $reflection = new \ReflectionClass(\App\Mcp\Servers\EducationServer::class);
        $defaults = $reflection->getDefaultProperties();

        $this->assertArrayHasKey('model', $defaults);
        $this->assertEquals(\App\Models\Education::class, $defaults['model']);
    }
}
