<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Models\Publication;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Publication::all();

        return response()->json($items);
    }
}
