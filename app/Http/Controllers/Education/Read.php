<?php

namespace App\Http\Controllers\Education;

use App\Models\Education;

class Read
{
    public function __invoke()
    {
        $items = Education::with('basics')->get();

        return response()->json($items);
    }
}
