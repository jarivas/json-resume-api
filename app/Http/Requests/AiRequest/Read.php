<?php

namespace App\Http\Requests\AiRequest;

use Illuminate\Foundation\Http\FormRequest;

class Read extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'integer|min:1|max:100',
        ];
    }
}
