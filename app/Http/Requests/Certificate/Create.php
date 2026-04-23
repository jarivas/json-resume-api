<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'required|string',
            'date' => 'required|date:Y-m-d',
            'issuer' => 'required|string',
            'url' => 'required|url',
        ];
    }
}
