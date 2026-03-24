<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportJson extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain', 'max:10240'],
            'disk' => ['sometimes', 'string', 'max:100'],
            'path' => ['sometimes', 'string', 'max:2048'],
        ];
    }
}
