<?php

namespace App\Http\Controllers\Interest;

use App\Http\Requests\Interest\Update as Request;
use App\Models\Interest;

class Update
{
    public function __invoke(Request $request, Interest $interest)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $interest->update($data);
        }

        return response()->json($interest->toArray());
    }
}
