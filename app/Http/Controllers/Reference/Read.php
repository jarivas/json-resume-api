<?php

namespace App\Http\Controllers\Reference;

use App\Models\Reference;

class Read
{
    public function __invoke()
    {
        $items = Reference::get();

        return response()->json($items);
    }
}
