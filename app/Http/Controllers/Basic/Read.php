<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Controllers\Controller;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Basic::all();

        return response()->json($items);
    }
}