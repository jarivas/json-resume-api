<?php

namespace App\Http\Controllers\Project;

use App\Models\Project;

class Read
{
    public function __invoke()
    {
        $items = Project::all();

        return response()->json($items);
    }
}
