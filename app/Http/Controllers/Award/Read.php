<?php

namespace App\Http\Controllers\Award;

use App\Models\Award;

class Read
{
    public function __invoke()
    {
        $items = Award::with('basics')->get();

        return response()->json($items);
    }
}
