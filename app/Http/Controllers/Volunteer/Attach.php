<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Volunteer;

class Attach extends Controller
{
    public function __invoke(Volunteer $volunteer, Basic $basic)
    {
        $basic->volunteers()->attach($volunteer);

        return response()->noContent();
    }
}
