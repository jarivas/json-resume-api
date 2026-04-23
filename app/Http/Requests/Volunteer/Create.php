<?php

namespace App\Http\Requests\Volunteer;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'organization' => 'required|string',
            'position' => 'required|string',
            'startDate' => 'required|date:Y-m-d',
            'endDate' => 'date:Y-m-d',
            'summary' => 'string',
        ];
    }
}
