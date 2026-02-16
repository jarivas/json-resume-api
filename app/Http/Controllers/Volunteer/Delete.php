<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;

class Delete extends Controller
{
    public function __invoke(Volunteer $volunteer)
    {
        return $volunteer->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Volunteer model');
    }
}
