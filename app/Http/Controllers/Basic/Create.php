<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Basic\Create as Request;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $basic = Basic::create($data);

        return response()->json($basic->toArray(), 201);
    }
}