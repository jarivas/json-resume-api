<?php

namespace App\Http\Controllers\Publication;

use App\Http\Requests\Publication\Update as Request;
use App\Models\Publication;

class Update
{
    public function __invoke(Request $request, Publication $publication)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $publication->update($data);
        }

        if ($request->has('basics')) {
            $publication->basics()->sync($request->get('basics'));
        }

        return response()->json($publication);
    }
}
