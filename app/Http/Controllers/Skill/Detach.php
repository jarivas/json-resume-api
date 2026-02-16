<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Skill;

class Detach extends Controller
{
    public function __invoke(Skill $skill, Basic $basic)
    {
        $basic->skills()->detach($skill);

        return response()->noContent();
    }
}
