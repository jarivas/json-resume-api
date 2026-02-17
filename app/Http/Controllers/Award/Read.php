<?php

namespace App\Http\Controllers\Award;

use App\Models\Award;

class Read
{
    public function __invoke()
    {
        $items = Award::all();

        return response()->json($items);
    }
}
