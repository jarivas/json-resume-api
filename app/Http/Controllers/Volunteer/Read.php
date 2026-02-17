<?php

namespace App\Http\Controllers\Volunteer;

use App\Models\Volunteer;

class Read
{
    public function __invoke()
    {
        $items = Volunteer::all();

        return response()->json($items);
    }
}
