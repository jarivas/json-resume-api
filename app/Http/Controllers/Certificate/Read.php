<?php

namespace App\Http\Controllers\Certificate;

use App\Models\Certificate;

class Read
{
    public function __invoke()
    {
        $items = Certificate::with('basics')->get();

        return response()->json($items);
    }
}
