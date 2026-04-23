<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'string',
            'publisher' => 'string',
            'releaseDate' => 'date:Y-m-d',
            'url' => 'url',
            'summary' => 'string',
        ];
    }
}
