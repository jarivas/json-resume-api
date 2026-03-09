<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class IsoServerTest extends TestCase
{
    public function test_iso_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\IsoServer::class));
    }
}
