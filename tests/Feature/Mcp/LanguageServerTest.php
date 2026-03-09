<?php

namespace Tests\Feature\Mcp;

use Tests\TestCase;

class LanguageServerTest extends TestCase
{
    public function test_language_server_class_exists()
    {
        $this->assertTrue(class_exists(\App\Mcp\Servers\LanguageServer::class));
    }
}
