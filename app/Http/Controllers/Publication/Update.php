<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\Update as Request;
use App\Models\Publication;

class Update extends Controller
{
    public function __invoke(Request $request, Publication $publication)
    {
        $data = $request->validated();

        $publication->update($data);

        return response()->json($publication);
    }
}
