<?php

namespace App\Http\Requests\Volunteer;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'organization' => 'string',
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