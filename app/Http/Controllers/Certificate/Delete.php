<?php

namespace App\Http\Controllers\Certificate;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Certificate;

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
