<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'required|string',
            'publisher' => 'required|string',
            'releaseDate' => 'required|date:Y-m-d',
            'url' => 'url',
            'summary' => 'required|string',
        ];
    }
}
