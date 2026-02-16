<?php

namespace App\Http\Controllers\Interest;

use App\Http\Controllers\Controller;
use App\Models\Interest;

class Delete extends Controller
{
    public function __invoke(Interest $interest)
    {
        return $interest->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Interest model');
    }
}
