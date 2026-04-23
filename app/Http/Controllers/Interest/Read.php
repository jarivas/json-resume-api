<?php

namespace App\Http\Controllers\Interest;

use App\Models\Interest;
use App\Http\Resources\InterestResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\InterestResource
 * @apiResourceModel App\Models\Interest
 */
class Read
{
    #[ResponseFromApiResource(InterestResource::class, model: Interest::class, collection: true)]
    public function __invoke()
    {
        $items = Interest::get();

        return InterestResource::collection($items);
    }
}
