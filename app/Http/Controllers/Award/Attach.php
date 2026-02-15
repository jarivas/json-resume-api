<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Basic;

class Attach extends Controller
{
    public function __invoke(Award $award, Basic $basic)
    {
        $basic->awards()->attach($award);

        return response()->noContent();
    }
}
