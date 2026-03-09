<?php

namespace App\Http\Controllers\Education;

use App\Models\Education;

class Read
{
    public function __invoke()
    {
        $items = Education::get();

        return response()->json($items);
    }
}
