<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Project Server')]
#[Version('0.0.1')]
#[Instructions('Server for project CRUD operations.')]
class ProjectServer extends Server
{
    protected array $tools = [];
    protected array $resources = [];
    protected array $prompts = [];

    public function index(array $query = []): array
    {
        return [];
    }

    public function show(string $id): array
    {
        return [];
    }
}
