<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Basic;

class Detach extends Controller
{
    public function __invoke(Award $award, Basic $basic)
    {
        $basic->awards()->detach($award);

        return response()->noContent();
    }
}
