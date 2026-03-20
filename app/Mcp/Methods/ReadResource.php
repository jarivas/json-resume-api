<?php

namespace App\Mcp\Methods;

use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Methods\ReadResource as BaseReadResource;
use Laravel\Mcp\Server\Resource;

class ReadResource extends BaseReadResource
{
    /**
     * @return callable(ResponseFactory): array<string, mixed>
     */
    protected function serializable(Resource $resource, string $uri): callable
    {
        return fn (ResponseFactory $factory): array => $factory->mergeStructuredContent(
            $factory->mergeMeta([
                'contents' => $factory->responses()->map(fn ($response): array => [
                    ...$response->content()->toResource($resource),
                    'uri' => $uri,
                ])->all(),
            ])
        );
    }
}
