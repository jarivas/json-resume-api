<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Skill;

class Attach extends Controller
{
    public function __invoke(Skill $skill, Basic $basic)
    {
        $basic->skills()->attach($skill);

        return response()->noContent();
    }
}
