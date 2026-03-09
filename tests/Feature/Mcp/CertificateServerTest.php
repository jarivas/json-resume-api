<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class CertificateServerTest extends TestCase
{
    public function test_certificate_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\CertificateServer::class));
    }
}
