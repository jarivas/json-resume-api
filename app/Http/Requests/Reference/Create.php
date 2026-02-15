<?php

namespace App\Http\Requests\Reference;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'reference' => 'required|string',
        ];
    }
}