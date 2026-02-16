<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Project;

class Attach extends Controller
{
    public function __invoke(Project $project, Basic $basic)
    {
        $basic->projects()->attach($project);

        return response()->noContent();
    }
}
