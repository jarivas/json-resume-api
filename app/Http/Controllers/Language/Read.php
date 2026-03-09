<?php

namespace App\Http\Controllers\Language;

use App\Models\Language;

class Read
{
    public function __invoke()
    {
        $items = Language::get();

        return response()->json($items);
    }
}
