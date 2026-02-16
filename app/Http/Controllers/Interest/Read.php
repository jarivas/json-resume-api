<?php

namespace App\Http\Controllers\Interest;

use App\Http\Controllers\Controller;
use App\Models\Interest;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Interest::all();

        return response()->json($items);
    }
}
