<?php

namespace App\Http\Controllers\Language;

use App\Http\Requests\Language\Create as Request;
use App\Models\Language;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $language = Language::create($data);
        
        if ($request->has('basics')) {
            $language->basics()->attach($request->get('basics'));
        }

        return response()->json($language->toArray(), 201);
    }
}
