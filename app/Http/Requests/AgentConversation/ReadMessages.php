<?php

namespace App\Http\Requests\AgentConversation;

use Illuminate\Foundation\Http\FormRequest;

class ReadMessages extends FormRequest
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
