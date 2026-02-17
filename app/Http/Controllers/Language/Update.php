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

        if (!empty($data)) {
            $language->update($data);
        }

        if ($request->has('basics')) {
            $language->basics()->sync($request->get('basics'));
        }

        return response()->json($language);
    }
}
