<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'publisher' => $this->publisher,
            'releaseDate' => $this->releaseDate,
            'url' => $this->url,
            'summary' => $this->summary,
        ];
    }
}
