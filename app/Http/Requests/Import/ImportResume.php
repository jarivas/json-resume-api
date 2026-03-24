<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportResume extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
            'disk' => ['sometimes', 'string', 'max:100'],
            'path' => ['sometimes', 'string', 'max:2048'],
            'keep_json' => ['sometimes', 'boolean'],
        ];
    }
}
