<?php

namespace App\Http\Requests\AgentConversation;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class ReadMessages extends FormRequest
{
    use ScribeBodyParameters;
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
