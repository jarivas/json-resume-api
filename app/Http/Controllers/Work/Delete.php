<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\Work;

class Delete extends Controller
{
    public function __invoke(Work $work)
    {
        return $work->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Work model');
    }
}
