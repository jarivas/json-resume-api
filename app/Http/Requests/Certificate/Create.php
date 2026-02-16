<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'date' => 'required|date:Y-m-d',
            'issuer' => 'required|string',
            'url' => 'required|url',
            'basics' => 'array',
            'basics.*' => 'ulid|exists:basics,id',
        ];
    }
}