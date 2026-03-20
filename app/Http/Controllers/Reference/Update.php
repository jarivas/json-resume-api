<?php

namespace App\Http\Controllers\Reference;

use App\Http\Requests\Reference\Update as Request;
use App\Models\Reference;
use Illuminate\Routing\Controller as BaseController;

class Update extends BaseController
{
    public function __invoke(Request $request, Reference $reference)
    {
        $data = $request->validated();

        if (! empty($data)) {
            $reference->update($data);
        }

        return response()->json($reference->toArray());
    }
}
