<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Http\Requests\Work\Create as Request;
use App\Models\Work;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $work = Work::create($data);

        if ($request->has('basics')) {
            $work->basics()->attach($request->get('basics'));
        }

        return response()->json($work->toArray(), 201);
    }
}
