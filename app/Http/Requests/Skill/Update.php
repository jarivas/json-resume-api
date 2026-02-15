<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'level' => 'string',
            'keywords' => 'array',
            'keywords.*' => 'string',
        ];
    }
}