<?php

namespace App\Http\Requests\Work;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'position' => 'string',
            'url' => 'url',
            'startDate' => 'string',
            'endDate' => 'string',
            'summary' => 'string',
            'highlights' => 'array',
            'highlights.*' => 'string',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}