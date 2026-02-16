<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'startDate' => 'required|date:Y-m-d',
            'endDate' => 'required|date:Y-m-d',
            'description' => 'required|string',
            'highlights' => 'required|array',
            'highlights.*' => 'required|string',
            'url' => 'url',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}