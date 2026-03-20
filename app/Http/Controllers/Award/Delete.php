<?php

namespace App\Http\Controllers\Award;

use App\Models\Award;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Award $award)
    {
        if (! $award->delete()) {
            throw new HttpException(400, 'Problem deleting the Award model');
        }

        return response()->noContent();
    }
}
