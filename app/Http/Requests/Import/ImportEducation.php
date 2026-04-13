<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class ImportEducation extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['sometimes', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:10240', 'required_without:url'],
            'url' => ['sometimes', 'url', 'required_without:file'],
        ];
    }
}
