<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Education\Update as Request;
use App\Models\Education;

class Update extends Controller
{
    public function __invoke(Request $request, Education $education)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $education->update($data);
        }

        if ($request->has('basics')) {
            $education->basics()->sync($request->get('basics'));
        }

        return response()->json($education);
    }
}
