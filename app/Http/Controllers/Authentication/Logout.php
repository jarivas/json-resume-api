<?php

namespace App\Http\Controllers\Authentication;

use App\Helpers\Http\Controllers\Authentication\Authentication;

class Logout
{
    use Authentication;

    public function __invoke()
    {
        $this->logoutHelper();

        return response()->noContent();
    }
}
