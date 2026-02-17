<?php

namespace App\Http\Controllers\Work;

use App\Models\Work;

class Read
{
    public function __invoke()
    {
        $items = Work::all();

        return response()->json($items);
    }
}
