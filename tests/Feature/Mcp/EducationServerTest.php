<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\EducationServer;
use Tests\TestCase;

class EducationServerTest extends TestCase
{
    public function test_education_server_class_exists()
    {
        $this->assertTrue(class_exists(EducationServer::class));
    }
}
