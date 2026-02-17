<?php

namespace App\Http\Controllers\Education;

use App\Http\Requests\Education\Create as Request;
use App\Models\Education;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $education = Education::create($data);

        if ($request->has('basics')) {
            $education->basics()->sync($request->get('basics'));
        }

        return response()->json($education->toArray(), 201);
    }
}
