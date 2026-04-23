<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
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
