<?php

namespace App\Http\Controllers\Reference;

use App\Models\Reference;
use App\Http\Resources\ReferenceResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\ReferenceResource
 * @apiResourceModel App\Models\Reference
 */
class Read
{
    #[ResponseFromApiResource(ReferenceResource::class, model: Reference::class, collection: true)]
    public function __invoke()
    {
        $items = Reference::get();

        return ReferenceResource::collection($items);
    }
}
