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
            'basic_id' => 'nullable|ulid|exists:basics,id',
        ];
    }
}