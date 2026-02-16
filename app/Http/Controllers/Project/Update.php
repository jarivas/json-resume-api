<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Update as Request;
use App\Models\Project;

class Update extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        $data = $request->validated();

        $project->update($data);

        return response()->json($project);
    }
}
