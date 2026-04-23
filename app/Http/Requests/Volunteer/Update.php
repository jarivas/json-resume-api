<?php

namespace App\Http\Requests\Volunteer;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'organization' => 'string',
            'position' => 'string',
            'startDate' => 'date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'summary' => 'string',
            'highlights' => 'array',
            'highlights.*' => 'string',
        ];
    }
}
