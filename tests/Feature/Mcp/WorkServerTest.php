<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\WorkServer;
use Tests\TestCase;

class WorkServerTest extends TestCase
{
    public function test_work_server_class_exists()
    {
        $this->assertTrue(class_exists(WorkServer::class));
    }
}
