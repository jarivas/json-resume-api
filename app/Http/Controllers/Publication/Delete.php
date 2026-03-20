<?php

namespace App\Http\Controllers\Publication;

use App\Models\Publication;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Publication $publication)
    {
        if (! $publication->delete()) {
            throw new HttpException(400, 'Problem deleting the Publication model');
        }

        return response()->noContent();
    }
}
