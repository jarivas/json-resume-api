<?php

namespace App\Http\Controllers\Interest;

use App\Models\Interest;

class Read
{
    public function __invoke()
    {
        $items = Interest::all();

        return response()->json($items);
    }
}
