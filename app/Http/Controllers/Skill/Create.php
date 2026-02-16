<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\Skill\Create as Request;
use App\Models\Skill;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $skill = Skill::create($data);

        return response()->json($skill->toArray(), 201);
    }
}
