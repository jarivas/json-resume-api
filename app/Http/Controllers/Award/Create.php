<?php

namespace App\Http\Controllers\Award;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Award\Create as Request;
use App\Models\Award;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $award = Award::create($data);

        return response()->json($award->toArray(), 201);
    }
}
