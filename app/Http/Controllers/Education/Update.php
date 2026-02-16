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

        $education->update($data);

        return response()->json($education);
    }
}
