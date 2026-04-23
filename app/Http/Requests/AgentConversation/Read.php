<?php

namespace App\Http\Requests\AgentConversation;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class Read extends FormRequest
{
    use ScribeBodyParameters;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'integer',
        ];
    }
}
