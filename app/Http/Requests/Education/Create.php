<?php

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules(): array
    {
        return [
            'institution' => 'required|string',
            'area' => 'required|string',
            'area' => 'required|string',
            'studyType' => 'required|string',
            'startDate' => 'required|date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'score' => 'string',
            'gpa' => 'string',
            'summary' => 'required|string',
            'courses' => 'array',
            'courses.*' => 'string',
        ];
    }
}
