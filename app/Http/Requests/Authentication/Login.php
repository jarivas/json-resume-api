<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Http\Requests\Authentication\Password as PasswordHelper;

class Login extends FormRequest
{
    use PasswordHelper;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => $this->passwordRequired(),
        ];
    }
}