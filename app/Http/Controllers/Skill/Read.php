<?php

namespace App\Http\Controllers\Skill;

use App\Models\Skill;

class Read
{
    public function __invoke()
    {
        $items = Skill::get();

        return response()->json($items);
    }
}
