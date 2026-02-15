<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'level' => 'required|string',
            'keywords' => 'required|array',
            'keywords.*' => 'required|string',
        ];
    }
}