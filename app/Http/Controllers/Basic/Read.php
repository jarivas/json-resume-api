<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;

class Read
{
    public function __invoke()
    {
        $items = Basic::all();

        return response()->json($items);
    }
}