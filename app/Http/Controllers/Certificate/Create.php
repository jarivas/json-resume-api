<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\Create as Request;
use App\Models\Certificate;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $certificate = Certificate::create($data);

        return response()->json($certificate->toArray(), 201);
    }
}
