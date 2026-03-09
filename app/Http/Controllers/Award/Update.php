<?php

namespace App\Http\Controllers\Award;

use App\Http\Requests\Award\Update as Request;
use App\Models\Award;

class Update
{
    public function __invoke(Request $request, Award $award)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $award->update($data);
        }

        return response()->json($award->toArray());
    }
}
