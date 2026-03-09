<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class EducationServerTest extends TestCase
{
    public function test_education_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\EducationServer::class));
    }
}
