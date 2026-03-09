<?php

namespace App\Http\Controllers\Education;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Education\Update as Request;
use App\Models\Education;

class Update extends BaseController
{
    public function __invoke(Request $request, Education $education)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $education->update($data);
        }

        return response()->json($education->toArray());
    }
}
