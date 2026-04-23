<?php

namespace App\Http\Controllers\Language;

use App\Models\Language;
use App\Http\Resources\LanguageResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\LanguageResource
 * @apiResourceModel App\Models\Language
 */
class Read
{
    #[ResponseFromApiResource(LanguageResource::class, model: Language::class, collection: true)]
    public function __invoke()
    {
        $items = Language::get();

        return LanguageResource::collection($items);
    }
}
