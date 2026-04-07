<?php

namespace App\Http\Controllers\Authentication;

use App\Services\Authentication\AuthenticationService;

class Logout
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function __invoke()
    {
        $this->authenticationService->logout();

        return response()->noContent();
    }
}
