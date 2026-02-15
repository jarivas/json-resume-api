<?php

namespace App\Http\Controllers\Basic;

use App\Http\Controllers\Controller;
use App\Models\Basic;

class ReadOne extends Controller
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
