<?php

namespace App\Http\Controllers\Work;

use App\Models\Work;
use App\Http\Resources\WorkResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\WorkResource
 * @apiResourceModel App\Models\Work
 */
class Read
{
    #[ResponseFromApiResource(WorkResource::class, model: Work::class, collection: true)]
    public function __invoke()
    {
        $items = Work::get();

        return WorkResource::collection($items);
    }
}
