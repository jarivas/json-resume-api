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

        if ($request->has('basics')) {
            $award->basics()->sync($request->get('basics'));
        }

        return response()->json($award->toArray());
    }
}
