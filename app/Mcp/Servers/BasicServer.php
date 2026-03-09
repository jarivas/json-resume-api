<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Basic;

#[Name('Basic Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]

class BasicServer extends Server
{
    use ReadServerTrait;

    protected string $model = Basic::class;

    public function index(array $query = []): array
    {
        if (empty($this->model)) {
            return [];
        }

        $perPage = isset($query['per_page']) ? (int) $query['per_page'] : 20;
        $page = isset($query['page']) ? (int) $query['page'] : 1;

        // Use model's $with to keep eager-loaded relations in a single source of truth
        $paginator = Basic::paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function ($basic) {
            return $basic->toArray();
        })->toArray();

        return [
            'data' => $data,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ];
    }

    public function show(string $id): array
    {
        $basic = Basic::find($id);

        if (! $basic) {
            return [];
        }

        return $basic->toArray();
    }
}
