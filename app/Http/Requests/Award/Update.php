<?php

namespace App\Http\Requests\Award;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Update extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'title' => 'string',
            'date' => 'date:Y-m-d',
            'awarder' => 'string',
            'summary' => 'string',
        ];
    }
}
