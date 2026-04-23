<?php

namespace App\Http\Requests\Interest;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'keywords' => 'required|array',
            'keywords.*' => 'required|string',
        ];
    }
}
