<?php

namespace App\Http\Controllers\Education;

use App\Models\Education;
use App\Http\Resources\EducationResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\EducationResource
 * @apiResourceModel App\Models\Education
 */
class Read
{
    #[ResponseFromApiResource(EducationResource::class, model: Education::class, collection: true)]
    public function __invoke()
    {
        $items = Education::get();

        return EducationResource::collection($items);
    }
}
