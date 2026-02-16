<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Models\Reference;

class Delete extends Controller
{
    public function __invoke(Reference $reference)
    {
        return $reference->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Reference model');
    }
}
