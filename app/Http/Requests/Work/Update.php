<?php

namespace App\Http\Requests\Work;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'name' => 'string',
            'position' => 'string',
            'url' => 'url',
            'startDate' => 'string',
            'endDate' => 'string',
            'summary' => 'string',
            'highlights' => 'array',
            'highlights.*' => 'string',
        ];
    }
}
