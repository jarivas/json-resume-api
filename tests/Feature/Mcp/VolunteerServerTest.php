<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class VolunteerServerTest extends TestCase
{
    public function test_volunteer_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\VolunteerServer::class));
    }
}
