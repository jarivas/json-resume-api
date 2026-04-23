<?php

namespace App\Http\Requests\Authentication;

use App\Helpers\Http\Requests\Authentication\Password as PasswordHelper;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\ScribeBodyParameters;

class ChangePassword extends FormRequest
{
    use PasswordHelper;
    use ScribeBodyParameters;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => $this->passwordRequired(),
        ];
    }
}
