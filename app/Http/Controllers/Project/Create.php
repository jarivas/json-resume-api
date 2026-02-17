<?php

namespace App\Http\Controllers\Project;

use App\Http\Requests\Project\Create as Request;
use App\Models\Project;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $project = Project::create($data);
        
        if ($request->has('basics')) {
            $project->basics()->attach($request->get('basics'));
        }

        return response()->json($project->toArray(), 201);
    }
}
