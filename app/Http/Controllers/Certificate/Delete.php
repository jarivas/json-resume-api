<?php

namespace App\Http\Controllers\Certificate;

use App\Models\Certificate;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Certificate $certificate)
    {
        if (! $certificate->delete()) {
            throw new HttpException(400, 'Problem deleting the Certificate model');
        }

        return response()->noContent();
    }
}
