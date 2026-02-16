<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Reference;

class Detach extends Controller
{
    public function __invoke(Reference $reference, Basic $basic)
    {
        $basic->references()->detach($reference);

        return response()->noContent();
    }
}
