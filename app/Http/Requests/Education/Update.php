<?php

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'institution' => 'string',
            'url' => 'url',
            'area' => 'string',
            'studyType' => 'string',
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'score' => 'string',
            'summary' => 'string',
            'courses' => 'array',
            'courses.*' => 'string',
        ];
    }
}
