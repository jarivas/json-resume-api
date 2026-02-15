<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Models\Award;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Award::all();

        return response()->json($items);
    }
}
