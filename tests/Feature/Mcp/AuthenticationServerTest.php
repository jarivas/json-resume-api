<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class AuthenticationServerTest extends TestCase
{
    public function test_authentication_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\AuthenticationServer::class));
    }
}
