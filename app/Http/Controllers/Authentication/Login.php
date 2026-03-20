<?php

namespace App\Http\Controllers\Authentication;

use App\Helpers\Http\Controllers\Authentication\Authentication;
use App\Http\Requests\Authentication\Login as Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Login
{
    use Authentication;

    public function __invoke(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $result = $this->loginHelper($credentials);

        if ($result === false) {
            throw new HttpException(404, 'Not found.');
        }

        return response()->json($result);
    }
}
