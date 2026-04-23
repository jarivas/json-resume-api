<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'institution' => $this->institution,
            'area' => $this->area,
            'studyType' => $this->studyType,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'score' => $this->score,
            'summary' => $this->summary,
        ];
    }
}
