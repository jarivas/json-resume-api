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
            'phone' => 'string',
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
            'awards' => 'array',
            'awards.*' => 'ulid|exists:awards,id',
            'certificates' => 'array',
            'certificates.*' => 'ulid|exists:certificates,id',
            'educations' => 'array',
            'educations.*' => 'ulid|exists:educations,id',
            'interests' => 'array',
            'interests.*' => 'ulid|exists:interests,id',
            'languages' => 'array',
            'languages.*' => 'ulid|exists:languages,id',
            'projects' => 'array',
            'projects.*' => 'ulid|exists:projects,id',
            'publications' => 'array',
            'publications.*' => 'ulid|exists:publications,id',
            'references' => 'array',
            'references.*' => 'ulid|exists:references,id',
            'skills' => 'array',
            'skills.*' => 'ulid|exists:skills,id',
            'volunteer' => 'array',
            'volunteer.*' => 'ulid|exists:volunteers,id',
            'work' => 'array',
            'work.*' => 'ulid|exists:works,id',
        ];
    }
}