<?php

namespace App\Http\Controllers\Education;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Education\Create as Request;
use App\Models\Education;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $education = Education::create($data);

        return response()->json($education->toArray(), 201);
    }
}
