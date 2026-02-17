<?php

namespace App\Http\Controllers\Skill;

use App\Http\Requests\Skill\Create as Request;
use App\Models\Skill;

class Create 
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $skill = Skill::create($data);

        if ($request->has('basics')) {
            $skill->basics()->attach($request->get('basics'));
        }

        return response()->json($skill->toArray(), 201);
    }
}
