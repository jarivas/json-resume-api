<?php

namespace App\Http\Controllers\Award;

use App\Http\Controllers\Controller;
use App\Http\Requests\Award\Create as Request;
use App\Models\Award;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $award = Award::create($data);
        
        if ($request->has('basics')) {
            $award->basics()->attach($request->get('basics'));
        }

        return response()->json($award->toArray(), 201);
    }
}
