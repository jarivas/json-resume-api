<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;

class Delete extends Controller
{
    public function __invoke(Project $project)
    {
        return $project->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Project model');
    }
}
