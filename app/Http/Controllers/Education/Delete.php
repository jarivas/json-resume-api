<?php

namespace App\Http\Controllers\Education;

use App\Models\Education;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Education $education)
    {
        if (! $education->delete()) {
            throw new HttpException(400, 'Problem deleting the Education model');
        }

        return response()->noContent();
    }
}
