<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'required|string',
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'description' => 'string',
            'roles' => 'array',
            'roles.*' => 'string',
            'highlights' => 'array',
            'highlights.*' => 'string',
            'keywords' => 'array',
            'keywords.*' => 'string',
            'url' => 'url',
        ];
    }
}
