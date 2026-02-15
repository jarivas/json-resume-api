<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Controllers\Controller;

class Delete extends Controller
{
    public function __invoke(Basic $model)
    {
        return $model->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the basic model');
    }
}