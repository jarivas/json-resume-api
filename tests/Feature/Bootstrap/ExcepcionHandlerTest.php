<?php

namespace Tests\Feature\Bootstrap;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ExcepcionHandlerTest extends TestCase
{
    public function test_probe_404_request_is_logged_as_notice(): void
    {
        Log::shouldReceive('log')
            ->once()
            ->withArgs(function ($level, $message, $context) {
                return $level === 'notice'
                    && $message === 'HTTP probe request not found'
                    && ($context['status'] ?? null) === 404
                    && ($context['path'] ?? null) === 'vpn/index.html'
                    && ($context['probe'] ?? false) === true
                    && isset($context['error_id']);
            });

        $response = $this->getJson('/vpn/index.html');

        $response->assertNotFound();
        $response->assertJsonPath('message', 'Not Found.');
        $response->assertJsonStructure(['error_id']);
    }

    public function test_regular_404_request_is_logged_as_warning(): void
    {
        Log::shouldReceive('log')
            ->once()
            ->withArgs(function ($level, $message, $context) {
                return $level === 'warning'
                    && $message === 'HTTP exception handled'
                    && ($context['status'] ?? null) === 404
                    && ($context['path'] ?? null) === 'missing-endpoint'
                    && ($context['probe'] ?? true) === false
                    && isset($context['error_id']);
            });

        $response = $this->getJson('/missing-endpoint');

        $response->assertNotFound();
        $response->assertJsonPath('message', 'Not Found.');
        $response->assertJsonStructure(['error_id']);
    }
}
