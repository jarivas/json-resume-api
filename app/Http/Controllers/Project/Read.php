<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Project::all();

        return response()->json($items);
    }
}
