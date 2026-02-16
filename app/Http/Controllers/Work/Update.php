<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Http\Requests\Work\Update as Request;
use App\Models\Work;

class Update extends Controller
{
    public function __invoke(Request $request, Work $work)
    {
        $data = $request->validated();

        $work->update($data);

        return response()->json($work);
    }
}
