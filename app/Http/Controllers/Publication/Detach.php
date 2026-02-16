<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Publication;

class Detach extends Controller
{
    public function __invoke(Publication $publication, Basic $basic)
    {
        $basic->publications()->detach($publication);

        return response()->noContent();
    }
}
