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

        $work = Work::create($data);

        return response()->json($work->toArray(), 201);
    }
}
