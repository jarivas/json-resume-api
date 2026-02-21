<?php

namespace App\Http\Controllers\Volunteer;

use App\Models\Volunteer;

class Read
{
    public function __invoke()
    {
        $items = Volunteer::with('basics')->get();

        return response()->json($items);
    }
}
