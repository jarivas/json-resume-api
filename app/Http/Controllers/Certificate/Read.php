<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Certificate;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Certificate::all();

        return response()->json($items);
    }
}
