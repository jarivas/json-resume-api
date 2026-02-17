<?php

namespace App\Http\Controllers\Certificate;

use App\Models\Certificate;

class Read
{
    public function __invoke()
    {
        $items = Certificate::all();

        return response()->json($items);
    }
}
