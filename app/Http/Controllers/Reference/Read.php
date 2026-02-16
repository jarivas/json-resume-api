<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Models\Reference;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Reference::all();

        return response()->json($items);
    }
}
