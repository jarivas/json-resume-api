<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Certificate;

class Detach extends Controller
{
    public function __invoke(Certificate $certificate, Basic $basic)
    {
        $basic->certificates()->detach($certificate);

        return response()->noContent();
    }
}
