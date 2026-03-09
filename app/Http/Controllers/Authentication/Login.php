<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Requests\Authentication\Login as Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Http\Controllers\Authentication\Login as LoginHelper;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Login
{
    use LoginHelper;

    public function __invoke(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        if (!$exists) {
            $this->error();
        }

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials, false)) {
            $this->error();
        }

        return $this->responseOk();
    }

    protected function error(): void
    {
        throw new HttpException(404, 'Not found.');
    }
}