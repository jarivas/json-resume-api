<?php

namespace App\Http\Controllers\Interest;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Interest;

class Delete
{
    public function __invoke(Interest $interest)
    {
        if (! $interest->delete()) {
            throw new HttpException(400, 'Problem deleting the Interest model');
        }

        return response()->noContent();
    }
}
