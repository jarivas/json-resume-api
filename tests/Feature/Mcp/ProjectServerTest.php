<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class ProjectServerTest extends TestCase
{
    public function test_project_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\ProjectServer::class));
    }
}
