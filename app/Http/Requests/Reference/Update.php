<?php

namespace App\Http\Requests\Reference;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'reference' => 'string',
            'basic_id' => 'nullable|ulid|exists:basics,id',
        ];
    }
}