<?php

namespace App\Http\Controllers\Authentication;

use App\Helpers\Http\Controllers\Authentication\Authentication;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RefreshToken
{
    use Authentication;

    public function __invoke()
    {
        $result = $this->refreshTokenHelper();

        if ($result === false) {
            throw new HttpException(404, 'Not found.');
        }

        return response()->json($result);
    }
}