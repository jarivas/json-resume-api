<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Reference;

class Attach extends Controller
{
    public function __invoke(Reference $reference, Basic $basic)
    {
        $basic->references()->attach($reference);

        return response()->noContent();
    }
}
