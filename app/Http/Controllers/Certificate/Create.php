<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Requests\Certificate\Create as Request;
use App\Models\Certificate;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $certificate = Certificate::create($data);

        if ($request->has('basics')) {
            $certificate->basics()->sync($request->get('basics'));
        }

        return response()->json($certificate->toArray(), 201);
    }
}
