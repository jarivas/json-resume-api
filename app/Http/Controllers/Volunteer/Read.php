<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Volunteer;

class Read extends Controller
{
    public function __invoke()
    {
        $items = Volunteer::all();

        return response()->json($items);
    }
}
