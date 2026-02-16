<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Volunteer\Create as Request;
use App\Models\Volunteer;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $volunteer = Volunteer::create($data);

        return response()->json($volunteer->toArray(), 201);
    }
}
