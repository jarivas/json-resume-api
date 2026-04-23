<?php

namespace App\Http\Requests\Award;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Create extends FormRequest
{
    use ScribeBodyParameters;
    public function rules()
    {
        return [
            'title' => 'required|string',
            'date' => 'required|date:Y-m-d',
            'awarder' => 'required|string',
            'summary' => 'required|string',
        ];
    }
}
