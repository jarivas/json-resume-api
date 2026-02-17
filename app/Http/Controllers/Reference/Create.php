<?php

namespace App\Http\Controllers\Reference;

use App\Http\Requests\Reference\Create as Request;
use App\Models\Reference;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $reference = Reference::create($data);

        if ($request->has('basics')) {
            $reference->basics()->attach($request->get('basics'));
        }

        return response()->json($reference->toArray(), 201);
    }
}
