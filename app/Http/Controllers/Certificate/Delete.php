<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Certificate;

class Delete extends Controller
{
    public function __invoke(Certificate $certificate)
    {
        return $certificate->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Certificate model');
    }
}
