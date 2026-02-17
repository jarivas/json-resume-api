<?php

namespace App\Http\Controllers\Award;

use App\Http\Requests\Award\Create as Request;
use App\Models\Award;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $award = Award::create($data);
        
        if ($request->has('basics')) {
            $award->basics()->attach($request->get('basics'));
        }

        return response()->json($award->toArray(), 201);
    }
}
