<?php

namespace App\Http\Controllers\Skill;

use App\Models\Skill;
use App\Http\Resources\SkillResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\SkillResource
 * @apiResourceModel App\Models\Skill
 */
class Read
{
    #[ResponseFromApiResource(SkillResource::class, model: Skill::class, collection: true)]
    public function __invoke()
    {
        $items = Skill::get();

        return SkillResource::collection($items);
    }
}
