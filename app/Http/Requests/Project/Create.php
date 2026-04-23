<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'required|string',
            'startDate' => 'required|date:Y-m-d',
            'endDate' => 'required|date:Y-m-d',
            'description' => 'required|string',
            'roles' => 'required|array',
            'roles.*' => 'required|string',
            'highlights' => 'required|array',
            'highlights.*' => 'required|string',
            'keywords' => 'required|array',
            'keywords.*' => 'required|string',
            'url' => 'url',
        ];
    }
}
