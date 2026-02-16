<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Certificate;

class Attach extends Controller
{
    public function __invoke(Certificate $certificate, Basic $basic)
    {
        $basic->certificates()->attach($certificate);

        return response()->noContent();
    }
}
