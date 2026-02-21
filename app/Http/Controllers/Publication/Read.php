<?php

namespace App\Http\Controllers\Publication;

use App\Models\Publication;

class Read
{
    public function __invoke()
    {
        $items = Publication::with('basics')->get();

        return response()->json($items);
    }
}
