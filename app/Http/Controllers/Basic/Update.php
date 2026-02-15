<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Basic\Update as Request;

class Update extends Controller
{
    public function __invoke(Request $request, Basic $basic)
    {
        $data = $request->validated();

        $basic->update($data);

        return response()->json($basic);
    }
}