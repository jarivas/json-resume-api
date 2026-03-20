<?php

namespace App\Http\Controllers\Basic;

use App\Http\Requests\Basic\Update as Request;
use App\Models\Basic;

class Update
{
    public function __invoke(Request $request, Basic $basic)
    {
        $data = $request->validated();

        $basic->update($data);

        return response()->json($basic);
    }
}
