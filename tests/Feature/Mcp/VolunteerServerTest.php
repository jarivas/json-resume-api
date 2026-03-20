<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\VolunteerServer;
use Tests\TestCase;

class VolunteerServerTest extends TestCase
{
    public function test_volunteer_server_class_exists()
    {
        $this->assertTrue(class_exists(VolunteerServer::class));
    }
}
