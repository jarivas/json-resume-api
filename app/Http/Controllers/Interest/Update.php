<?php

namespace App\Http\Controllers\Interest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Interest\Update as Request;
use App\Models\Interest;

class Update extends Controller
{
    public function __invoke(Request $request, Interest $interest)
    {
        $data = $request->validated();

        $interest->update($data);

        return response()->json($interest);
    }
}
