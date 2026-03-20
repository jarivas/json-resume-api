<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;

class ReadOne
{
    public function __invoke(Basic $basic)
    {
        return response()->json($basic);
    }
}
