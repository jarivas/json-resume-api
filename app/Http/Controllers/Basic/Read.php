<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;

class Read
{
    public function __invoke()
    {
        $basic = Basic::first();

        return response()->json($basic);
    }
}
