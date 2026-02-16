<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Work;

class Detach extends Controller
{
    public function __invoke(Work $work, Basic $basic)
    {
        $basic->works()->detach($work);

        return response()->noContent();
    }
}
