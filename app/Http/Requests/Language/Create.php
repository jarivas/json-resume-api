<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'language' => 'required|string',
            'fluency' => 'required|string',
        ];
    }
}