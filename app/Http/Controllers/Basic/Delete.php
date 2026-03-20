<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Basic $basic)
    {
        if (! $basic->delete()) {
            throw new HttpException(400, 'Problem deleting the basic model');
        }

        return response()->noContent();
    }
}
