<?php

namespace App\Http\Controllers\Education;

use App\Models\Education;

class Read
{
    public function __invoke()
    {
        $items = Education::all();

        return response()->json($items);
    }
}
