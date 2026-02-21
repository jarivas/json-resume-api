<?php

namespace App\Http\Controllers\Reference;

use App\Models\Reference;

class Read
{
    public function __invoke()
    {
        $items = Reference::with('basics')->get();

        return response()->json($items);
    }
}
