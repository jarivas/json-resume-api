<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\Update as Request;
use App\Models\Certificate;

class Update extends Controller
{
    public function __invoke(Request $request, Certificate $certificate)
    {
        $data = $request->validated();

        $certificate->update($data);

        return response()->json($certificate);
    }
}
