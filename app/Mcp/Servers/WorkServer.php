<?php

namespace App\Mcp\Servers;

use App\Mcp\Methods\ReadResource;
use App\Mcp\Resources\WorkResource;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Work;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Work Server')]
#[Version('0.0.1')]
#[Instructions('Server for work experience CRUD operations.')]

class WorkServer extends Server
{
    use ReadServerTrait;

    protected string $model = Work::class;

    /**
     * @var array<int, class-string<\\Laravel\\Mcp\\Server\\Resource>>
     */
    protected array $resources = [
        WorkResource::class,
    ];

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
