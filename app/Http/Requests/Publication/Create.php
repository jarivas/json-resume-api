<?php

namespace App\Http\Requests\Publication;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
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