<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class AwardServerTest extends TestCase
{
    public function test_award_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\AwardServer::class));
    }
}
