<?php

namespace App\Http\Controllers\Project;

use App\Http\Requests\Project\Update as Request;
use App\Models\Project;

class Update
{
    public function __invoke(Request $request, Project $project)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $project->update($data);
        }

        if ($request->has('basics')) {
            $project->basics()->sync($request->get('basics'));
        }

        return response()->json($project);
    }
}
