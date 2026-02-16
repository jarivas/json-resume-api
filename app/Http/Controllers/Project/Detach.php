<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Project;

class Detach extends Controller
{
    public function __invoke(Project $project, Basic $basic)
    {
        $basic->projects()->detach($project);

        return response()->noContent();
    }
}
