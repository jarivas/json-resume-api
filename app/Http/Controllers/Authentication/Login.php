<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Requests\Authentication\Login as Request;
use App\Services\Authentication\AuthenticationService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Login
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function __invoke(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        $result = $this->authenticationService->login($credentials);

        if ($result === false) {
            throw new HttpException(404, 'Not found.');
        }

        return response()->json($result);
    }
}
