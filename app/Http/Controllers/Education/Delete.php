<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Education;

class Delete extends Controller
{
    public function __invoke(Education $education)
    {
        return $education->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Education model');
    }
}
