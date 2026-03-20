<?php

namespace App\Mcp\Resources;

use App\Models\Work;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('work')]
#[Description('Work experience resource')]
#[MimeType('application/json')]
class WorkResource extends Resource
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $model = Work::query();

        if ($id = $request->get('id')) {
            $item = $model->find($id);

            return $item ? Response::structured(['contents' => [$item->toArray()]]) : Response::structured(['contents' => []]);
        }

        $items = $model->limit(50)->get()->map(fn ($i) => $i->toArray())->all();

        return Response::structured(['contents' => $items]);
    }
}
