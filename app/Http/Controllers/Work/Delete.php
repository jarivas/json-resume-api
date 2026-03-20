<?php

namespace App\Http\Controllers\Work;

use App\Models\Work;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Work $work)
    {
        if (! $work->delete()) {
            throw new HttpException(400, 'Problem deleting the Work model');
        }

        return response()->noContent();
    }
}
