<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\Update as Request;
use App\Models\Language;

class Update extends Controller
{
    public function __invoke(Request $request, Language $language)
    {
        $data = $request->validated();

        $language->update($data);

        return response()->json($language);
    }
}
