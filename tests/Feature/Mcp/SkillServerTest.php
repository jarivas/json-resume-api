<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class SkillServerTest extends TestCase
{
    public function test_skill_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\SkillServer::class));
    }
}
