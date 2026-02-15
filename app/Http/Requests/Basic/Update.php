<?php

namespace App\Http\Requests\Basic;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'string',
            'label' => 'string',
            'email' => 'email',
            'url' => 'url',
            'summary' => 'string',
            'location' => 'array',
            'location.address' => 'string',
            'location.postalCode' => 'string',
            'location.city' => 'string',
            'location.countryCode' => 'string',
            'location.region' => 'string',
        ];
    }
}