<?php

namespace App\Http\Controllers\Reference;

use App\Models\Reference;

class Read
{
    public function __invoke()
    {
        $items = Reference::all();

        return response()->json($items);
    }
}
