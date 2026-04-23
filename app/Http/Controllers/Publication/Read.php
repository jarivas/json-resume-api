<?php

namespace App\Http\Controllers\Publication;

use App\Models\Publication;
use App\Http\Resources\PublicationResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\PublicationResource
 * @apiResourceModel App\Models\Publication
 */
class Read
{
    #[ResponseFromApiResource(PublicationResource::class, model: Publication::class, collection: true)]
    public function __invoke()
    {
        $items = Publication::get();

        return PublicationResource::collection($items);
    }
}
