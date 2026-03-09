<?php

namespace App\Http\Controllers\Project;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Project\Create as Request;
use App\Models\Project;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $project = Project::create($data);

        return response()->json($project->toArray(), 201);
    }
}
