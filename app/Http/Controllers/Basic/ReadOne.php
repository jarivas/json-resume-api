<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;

class ReadOne
{
    public function __invoke(Basic $basic)
    {
        $basic->load([
            'work',
            'volunteer',
            'education',
            'awards',
            'certificates',
            'publications',
            'skills',
            'languages',
            'interests',
            'references',
            'projects'
        ]);

        return response()->json($basic);
    }
}
