<?php

namespace App\Http\Controllers\Language;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Language\Create as Request;
use App\Models\Language;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $language = Language::create($data);

        return response()->json($language->toArray(), 201);
    }
}
