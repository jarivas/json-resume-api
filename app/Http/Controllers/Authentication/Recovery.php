<?php

namespace App\Http\Controllers\Authentication;

use App\Services\Authentication\AuthenticationService;
use RuntimeException;

class Recovery
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function __invoke()
    {
        $success = $this->authenticationService->recovery();

        if (! $success) {
            throw new RuntimeException('Failed to send recovery email. Please try again later.');
        }

        return response()->noContent();
    }
}
