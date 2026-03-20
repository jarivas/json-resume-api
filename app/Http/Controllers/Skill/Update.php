<?php

namespace App\Http\Controllers\Skill;

use App\Http\Requests\Skill\Update as Request;
use App\Models\Skill;

class Update
{
    public function __invoke(Request $request, Skill $skill)
    {
        $data = $request->validated();

        if (! empty($data)) {
            $skill->update($data);
        }

        return response()->json($skill->toArray());
    }
}
