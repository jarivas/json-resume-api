<?php

namespace App\Http\Requests\Award;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string',
            'date' => 'required|date:Y-m-d',
            'awarder' => 'required|string',
            'summary' => 'required|string',
        ];
    }
}