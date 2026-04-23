<?php

namespace App\Http\Requests\Interest;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'string',
            'keywords' => 'array',
            'keywords.*' => 'string',
        ];
    }
}
