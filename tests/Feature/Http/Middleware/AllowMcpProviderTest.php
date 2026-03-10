<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\AllowMcpProvider;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AllowMcpProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(AllowMcpProvider::class)->get('/_mcp-protected', fn () =>
            response()->json(['ok' => true])
        );
    }

    public function test_it_allows_exact_ip(): void
    {
        config()->set('mcp.allowed_ips', ['10.0.0.5']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])
            ->getJson('/_mcp-protected')
            ->assertOk();
    }

    public function test_it_denies_non_matching_ip(): void
    {
        config()->set('mcp.allowed_ips', ['10.0.0.5']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])
            ->getJson('/_mcp-protected')
            ->assertForbidden();
    }
}