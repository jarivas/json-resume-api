<?php

namespace App\Helpers\Http\Requests\Authentication;

use Illuminate\Validation\Rules\Password as PasswordRule;

trait Password
{
    public function passwordRule(): array
    {
        return [
            'string',
            PasswordRule::min(8)->mixedCase()->numbers(),
        ];
    }

    public function passwordRequired(): array
    {
        return array_merge(['required'], $this->passwordRule());
    }
}
