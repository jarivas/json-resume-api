<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

class MissingRouteReturns404Test extends TestCase
{
    public function test_missing_web_route_returns_404(): void
    {
        $this->get('/ruta-que-no-existe')
            ->assertNotFound();
    }

    public function test_missing_json_route_returns_404(): void
    {
        $this->getJson('/api/ruta-que-no-existe')
            ->assertNotFound();
    }
}
