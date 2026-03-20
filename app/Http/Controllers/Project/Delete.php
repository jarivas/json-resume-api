<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Project $project)
    {
        if (! $project->delete()) {
            throw new HttpException(400, 'Problem deleting the Project model');
        }

        return response()->noContent();
    }
}
