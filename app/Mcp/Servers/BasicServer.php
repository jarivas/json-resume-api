<?php

namespace App\Mcp\Servers;

use App\Mcp\Methods\ReadResource;
use App\Mcp\Resources\BasicResource;
use App\Mcp\Servers\Traits\ReadServerTrait;
use App\Models\Basic;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Basic Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]

class BasicServer extends Server
{
    use ReadServerTrait;

    protected string $model = Basic::class;

    /**
     * @var array<int, class-string<\\Laravel\\Mcp\\Server\\Resource>>
     */
    protected array $resources = [
        BasicResource::class,
    ];

    /**
     * Override methods to use app-level ReadResource so structuredContent is propagated.
     *
     * @var array<string, class-string>
     */
    protected array $methods = [
        'resources/read' => ReadResource::class,
    ];

    public function index(array $query = []): array
    {
        if (empty($this->model)) {
            return [];
        }

        $perPage = isset($query['per_page']) ? (int) $query['per_page'] : 20;
        $page = isset($query['page']) ? (int) $query['page'] : 1;

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
