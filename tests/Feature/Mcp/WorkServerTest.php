<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class WorkServerTest extends TestCase
{
    public function test_work_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\WorkServer::class));
    }
}
