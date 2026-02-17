<?php

namespace App\Http\Controllers\Interest;

use App\Http\Requests\Interest\Create as Request;
use App\Models\Interest;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $interest = Interest::create($data);
        
        if ($request->has('basics')) {
            $interest->basics()->attach($request->get('basics'));
        }

        return response()->json($interest->toArray(), 201);
    }
}
