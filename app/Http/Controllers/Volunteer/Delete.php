<?php

namespace App\Http\Controllers\Volunteer;

use App\Models\Volunteer;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Volunteer $volunteer)
    {
        if (! $volunteer->delete()) {
            throw new HttpException(400, 'Problem deleting the Volunteer model');
        }

        return response()->noContent();
    }
}
