<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\PublicationServer;
use Tests\TestCase;

class PublicationServerTest extends TestCase
{
    public function test_publication_server_class_exists()
    {
        $this->assertTrue(class_exists(PublicationServer::class));
    }
}
