<?php

namespace App\Http\Controllers\Award;

use App\Models\Award;
use App\Http\Resources\AwardResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

class Read
{
    #[ResponseFromApiResource(AwardResource::class, model: Award::class, collection: true)]
    public function __invoke()
    {
        $items = Award::get();

        return AwardResource::collection($items);
    }
}

