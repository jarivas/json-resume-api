<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\ProjectServer;
use Tests\TestCase;

class ProjectServerTest extends TestCase
{
    public function test_project_server_class_exists()
    {
        $this->assertTrue(class_exists(ProjectServer::class));
    }
}
