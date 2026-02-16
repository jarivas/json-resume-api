<?php

namespace App\Http\Requests\Award;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'string',
            'date' => 'date:Y-m-d',
            'awarder' => 'string',
            'summary' => 'string',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}