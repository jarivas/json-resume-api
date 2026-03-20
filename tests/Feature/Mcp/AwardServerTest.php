<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\AwardServer;
use Tests\TestCase;

class AwardServerTest extends TestCase
{
    public function test_award_server_class_exists()
    {
        $this->assertTrue(class_exists(AwardServer::class));
    }
}
