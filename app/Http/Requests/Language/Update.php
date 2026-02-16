<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'language' => 'string',
            'fluency' => 'string',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}