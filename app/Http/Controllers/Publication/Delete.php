<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Models\Publication;

class Delete extends Controller
{
    public function __invoke(Publication $publication)
    {
        return $publication->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Publication model');
    }
}
