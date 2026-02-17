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

        if (!empty($data)) {
            $reference->update($data);
        }

        if ($request->has('basics')) {
            $reference->basics()->sync($request->get('basics'));
        }

        return response()->json($reference);
    }
}
