<?php

namespace App\Http\Controllers\Education;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Education;

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
