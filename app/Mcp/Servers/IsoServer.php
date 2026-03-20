<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('ISO Server')]
#[Version('0.0.1')]
#[Instructions('Server for ISO data: countries, currencies, languages.')]
class IsoServer extends Server
{
    protected array $tools = [];

    protected array $resources = [];

    protected array $prompts = [];

    public function countries(array $query = []): array
    {
        return [];
    }

    public function currencies(array $query = []): array
    {
        return [];
    }

    public function languages(array $query = []): array
    {
        return [];
    }
}
