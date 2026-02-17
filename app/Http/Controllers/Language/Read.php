<?php

namespace App\Http\Controllers\Language;

use App\Models\Language;

class Read
{
    public function __invoke()
    {
        $items = Language::all();

        return response()->json($items);
    }
}
