<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class InterestServerTest extends TestCase
{
    public function test_interest_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\InterestServer::class));
    }
}
