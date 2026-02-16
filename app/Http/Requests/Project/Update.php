<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'description' => 'string',
            'highlights' => 'array',
            'highlights.*' => 'string',
            'url' => 'url',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}