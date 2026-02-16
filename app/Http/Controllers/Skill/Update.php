<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Http\Requests\Skill\Update as Request;
use App\Models\Skill;

class Update extends Controller
{
    public function __invoke(Request $request, Skill $skill)
    {
        $data = $request->validated();

        $skill->update($data);

        return response()->json($skill);
    }
}
