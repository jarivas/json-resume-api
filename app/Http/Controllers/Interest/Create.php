<?php

namespace App\Http\Controllers\Interest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Interest\Create as Request;
use App\Models\Interest;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $interest = Interest::create($data);

        return response()->json($interest->toArray(), 201);
    }
}
