<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\Controller;
use App\Models\Language;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Language::all();

        return response()->json($items);
    }
}
