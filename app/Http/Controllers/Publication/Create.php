<?php

namespace App\Http\Controllers\Publication;

use App\Http\Requests\Publication\Create as Request;
use App\Models\Publication;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $publication = Publication::create($data);
        
        if ($request->has('basics')) {
            $publication->basics()->attach($request->get('basics'));
        }

        return response()->json($publication->toArray(), 201);
    }
}
