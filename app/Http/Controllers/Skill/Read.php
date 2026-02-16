<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Models\Skill;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Skill::all();

        return response()->json($items);
    }
}
