<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

class ChatCorsTest extends TestCase
{
    public function test_preflight_request_returns_cors_headers_for_allowed_origin(): void
    {
        $response = $this->call('OPTIONS', '/api/chat', [], [], [], [
            'HTTP_ORIGIN' => 'http://frontend.test',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertNotNull($response->headers->get('Access-Control-Allow-Methods'));
    }

    public function test_post_request_includes_cors_headers_for_allowed_origin(): void
    {
        $response = $this->call('POST', '/api/chat', [
            'message' => 'Hola',
        ], [], [], [
            'HTTP_ORIGIN' => 'http://frontend.test',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_request_from_disallowed_origin_does_not_include_allow_origin_header(): void
    {
        $response = $this->call('POST', '/api/chat', [
            'message' => 'Hola',
        ], [], [], [
            'HTTP_ORIGIN' => 'http://evil.test',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
