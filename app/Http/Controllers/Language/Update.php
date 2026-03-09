<?php

namespace App\Http\Controllers\Language;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Language\Update as Request;
use App\Models\Language;

class Update extends BaseController
{
    public function __invoke(Request $request, Language $language)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $language->update($data);
        }

        return response()->json($language->toArray());
    }
}
