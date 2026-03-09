<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class Chat extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string',
            'session_id' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];
    }
}
