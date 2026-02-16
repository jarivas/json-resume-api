<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\Create as Request;
use App\Models\Project;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $project = Project::create($data);

        return response()->json($project->toArray(), 201);
    }
}
