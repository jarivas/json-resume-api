<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class ReferenceServerTest extends TestCase
{
    public function test_reference_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\ReferenceServer::class));
    }
}
