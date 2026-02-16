<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Http\Requests\Award\Update as Request;
use App\Models\Award;

class Update extends Controller
{
    public function __invoke(Request $request, Award $award)
    {
        $data = $request->validated();

        $award->update($data);

        if ($request->has('basics')) {
            $award->basics()->sync($request->get('basics'));
        }

        return response()->json($award);
    }
}
