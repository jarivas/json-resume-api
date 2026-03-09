<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Award;

#[Name('Award Server')]
#[Version('0.0.1')]
#[Instructions('Server for award CRUD operations.')]

class AwardServer extends Server
{
    use ReadServerTrait;

    protected string $model = Award::class;

    public function index(array $query = []): array
    {
        return $this->readIndex($query);
    }

    public function show(string $id): array
    {
        return $this->readShow($id);
    }
}
