<?php

namespace App\Http\Controllers\Interest;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Interest;

class Detach extends Controller
{
    public function __invoke(Interest $interest, Basic $basic)
    {
        $basic->interests()->detach($interest);

        return response()->noContent();
    }
}
