<?php

namespace App\Mcp\Servers;

use App\Mcp\Methods\ReadResource;
use App\Mcp\Resources\SkillResource;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Skill;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Skill Server')]
#[Version('0.0.1')]
#[Instructions('Server for skill CRUD operations.')]

class SkillServer extends Server
{
    use ReadServerTrait;

    protected string $model = Skill::class;

    /**
     * @var array<int, class-string<\\Laravel\\Mcp\\Server\\Resource>>
     */
    protected array $resources = [
        SkillResource::class,
    ];

    /**
     * Use app-level ReadResource so Response::structured is emitted correctly.
     *
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
