<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Education;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Education::all();

        return response()->json($items);
    }
}
