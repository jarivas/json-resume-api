<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\Login as Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Http\Controllers\Authentication\Login as LoginHelper;

class Login extends Controller
{
    use LoginHelper;

    public function __invoke(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        if (!$exists) {
            $message = 'The provided email does not exist.';
            return $this->getErrorResponse('email', $message, 400);
        }

        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials, false)) {
            $message = 'The provided password is incorrect.';
            return $this->getErrorResponse('password', $message, 400);
        }

        return $this->responseOk();
    }
}