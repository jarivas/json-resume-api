<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reference\Create as Request;
use App\Models\Reference;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $reference = Reference::create($data);

        return response()->json($reference->toArray(), 201);
    }
}
