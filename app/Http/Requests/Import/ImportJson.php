<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class ImportJson extends FormRequest
{
    use ScribeBodyParameters;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain', 'max:10240'],
        ];
    }
}
