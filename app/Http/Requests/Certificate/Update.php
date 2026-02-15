<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'date' => 'date:Y-m-d',
            'issuer' => 'string',
            'url' => 'url',
        ];
    }
}