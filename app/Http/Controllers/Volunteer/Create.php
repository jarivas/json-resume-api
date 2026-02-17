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

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $volunteer = Volunteer::create($data);

        if ($request->has('basics')) {
            $volunteer->basics()->attach($request->get('basics'));
        }

        return response()->json($volunteer->toArray(), 201);
    }
}
