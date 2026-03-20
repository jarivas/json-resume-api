<?php

namespace App\Http\Controllers\Publication;

use App\Http\Requests\Publication\Update as Request;
use App\Models\Publication;

class Update
{
    public function __invoke(Request $request, Publication $publication)
    {
        $data = $request->validated();

        if (! empty($data)) {
            $publication->update($data);
        }

        return response()->json($publication->toArray());
    }
}
