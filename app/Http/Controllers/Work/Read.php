<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\Work;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Work::all();

        return response()->json($items);
    }
}
