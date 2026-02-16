<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Education;

class Attach extends Controller
{
    public function __invoke(Education $education, Basic $basic)
    {
        $basic->educations()->attach($education);

        return response()->noContent();
    }
}
