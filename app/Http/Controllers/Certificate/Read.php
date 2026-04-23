<?php

namespace App\Http\Controllers\Certificate;

use App\Models\Certificate;
use App\Http\Resources\CertificateResource;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

/**
 * @apiResourceCollection App\Http\Resources\CertificateResource
 * @apiResourceModel App\Models\Certificate
 */
class Read
{
    #[ResponseFromApiResource(CertificateResource::class, model: Certificate::class, collection: true)]
    public function __invoke()
    {
        $items = Certificate::get();

        return CertificateResource::collection($items);
    }
}
