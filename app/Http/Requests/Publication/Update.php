<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
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