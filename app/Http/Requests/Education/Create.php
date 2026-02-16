<?php

namespace App\Http\Requests\Education;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'institution' => 'required|string',
            'url' => 'url',
            'area' => 'required|string',
            'studyType' => 'required|string',
            'startDate' => 'required|date:Y-m-d',
            'endDate' => 'required|date:Y-m-d',
            'score' => 'string',
            'summary' => 'required|string',
            'courses' => 'array',
            'courses.*' => 'string',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}