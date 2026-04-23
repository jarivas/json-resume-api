<?php

namespace App\Http\Requests\Work;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'required|string',
            'position' => 'required|string',
            'url' => 'url',
            'startDate' => 'required|string',
            'endDate' => 'required|string',
            'summary' => 'required|string',
            'highlights' => 'required|array',
            'highlights.*' => 'required|string',
        ];
    }
}
