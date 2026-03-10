<?php

namespace App\Http\Controllers\Authentication;

use App\Helpers\Http\Controllers\Authentication\Authentication;
use RuntimeException;

class Recovery
{
    use Authentication;

    public function __invoke()
    {
        $success = $this->recoveryHelper();

        if (!$success) {
            throw new RuntimeException('Failed to send recovery email. Please try again later.');
        }

        return response()->noContent();
    }
}