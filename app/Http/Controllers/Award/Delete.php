<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Models\Award;

class Delete extends Controller
{
    public function __invoke(Award $award)
    {
        return $award->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Award model');
    }
}
