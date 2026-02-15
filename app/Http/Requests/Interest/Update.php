<?php

namespace App\Http\Requests\Interest;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'keywords' => 'array',
            'keywords.*' => 'string',
        ];
    }
}