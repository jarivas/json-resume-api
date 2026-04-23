<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\ProjectResource
 * @apiResourceModel App\Models\Project
 */
class Read
{
    #[ResponseFromApiResource(ProjectResource::class, model: Project::class, collection: true)]
    public function __invoke()
    {
        $items = Project::get();

        return ProjectResource::collection($items);
    }
}
