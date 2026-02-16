<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\Create as Request;
use App\Models\Language;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $language = Language::create($data);

        return response()->json($language->toArray(), 201);
    }
}
