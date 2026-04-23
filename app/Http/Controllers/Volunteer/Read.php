<?php

namespace App\Http\Controllers\Volunteer;

use App\Models\Volunteer;
use App\Http\Resources\VolunteerResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\VolunteerResource
 * @apiResourceModel App\Models\Volunteer
 */
class Read
{
    #[ResponseFromApiResource(VolunteerResource::class, model: Volunteer::class, collection: true)]
    public function __invoke()
    {
        $items = Volunteer::get();

        return VolunteerResource::collection($items);
    }
}
