<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organization' => $this->organization,
            'position' => $this->position,
            'url' => $this->url,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'summary' => $this->summary,
            'highlights' => $this->highlights,
        ];
    }
}
