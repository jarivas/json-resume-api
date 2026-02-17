<?php

namespace App\Http\Controllers\Work;

use App\Http\Requests\Work\Update as Request;
use App\Models\Work;

class Update
{
    public function __invoke(Request $request, Work $work)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $work->update($data);
        }

        if ($request->has('basics')) {
            $work->basics()->sync($request->get('basics'));
        }

        return response()->json($work);
    }
}
