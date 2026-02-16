<?php

namespace App\Http\Controllers\Reference;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reference\Update as Request;
use App\Models\Reference;

class Update extends Controller
{
    public function __invoke(Request $request, Reference $reference)
    {
        $data = $request->validated();

        $reference->update($data);

        return response()->json($reference);
    }
}
