<?php

namespace App\Mcp\Servers;

use App\Mcp\Methods\ReadResource;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Reference;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Reference Server')]
#[Version('0.0.1')]
#[Instructions('Server for reference CRUD operations.')]

class ReferenceServer extends Server
{
    use ReadServerTrait;

    protected string $model = Reference::class;

    /**
     * @var array<string, class-string>
     */
    protected array $methods = [
        'resources/read' => ReadResource::class,
    ];

    public function index(array $query = []): array
    {
        return $this->readIndex($query);
    }

    public function show(string $id): array
    {
        return $this->readShow($id);
    }
}
