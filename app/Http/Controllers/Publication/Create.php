<?php

namespace App\Http\Controllers\Publication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\Create as Request;
use App\Models\Publication;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $publication = Publication::create($data);

        return response()->json($publication->toArray(), 201);
    }
}
