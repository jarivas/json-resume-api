<?php

namespace App\Http\Controllers\Authentication;

use App\Services\Authentication\AuthenticationService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RefreshToken
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function __invoke()
    {
        $result = $this->authenticationService->refreshToken();

        if ($result === false) {
            throw new HttpException(404, 'Not found.');
        }

        return response()->json($result);
    }
}
