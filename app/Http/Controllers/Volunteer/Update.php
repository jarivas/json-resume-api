<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Volunteer\Update as Request;
use App\Models\Volunteer;

class Update extends Controller
{
    public function __invoke(Request $request, Volunteer $volunteer)
    {
        $data = $request->validated();

        $volunteer->update($data);

        return response()->json($volunteer);
    }
}
