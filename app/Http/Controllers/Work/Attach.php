<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Work;

class Attach extends Controller
{
    public function __invoke(Work $work, Basic $basic)
    {
        $basic->works()->attach($work);

        return response()->noContent();
    }
}
