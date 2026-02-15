<?php

namespace App\Http\Requests\Basic;

use Illuminate\Foundation\Http\FormRequest;

class Create extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'label' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'url' => 'url',
            'summary' => 'string',
            'location' => 'array',
            'location.address' => 'string',
            'location.postalCode' => 'string',
            'location.city' => 'string',
            'location.countryCode' => 'string',
            'location.region' => 'string',
            'profiles' => 'array',
            'profiles.*.network' => 'string',
            'profiles.*.username' => 'string',
            'profiles.*.url'=> 'url',
        ];
    }
}