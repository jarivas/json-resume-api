<?php

namespace App\Http\Controllers\Education;

use App\Http\Controllers\Controller;
use App\Http\Requests\Education\Create as Request;
use App\Models\Education;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $education = Education::create($data);

        return response()->json($education->toArray(), 201);
    }
}
