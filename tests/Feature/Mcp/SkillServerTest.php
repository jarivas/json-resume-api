<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\SkillServer;
use Tests\TestCase;

class SkillServerTest extends TestCase
{
    public function test_skill_server_class_exists()
    {
        $this->assertTrue(class_exists(SkillServer::class));
    }
}
