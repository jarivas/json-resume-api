<?php

namespace App\Http\Controllers\Reference;

use App\Models\Reference;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Reference $reference)
    {
        if (! $reference->delete()) {
            throw new HttpException(400, 'Problem deleting the Reference model');
        }

        return response()->noContent();
    }
}
