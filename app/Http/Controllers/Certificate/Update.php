<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Requests\Certificate\Update as Request;
use App\Models\Certificate;

class Update
{
    public function __invoke(Request $request, Certificate $certificate)
    {
        $data = $request->validated();

        $certificate->update($data);

        if ($request->has('basics')) {
            $certificate->basics()->sync($request->get('basics'));
        }

        return response()->json($certificate);
    }
}
