<?php

namespace App\Http\Controllers\Interest;

use App\Http\Requests\Interest\Create as Request;
use App\Models\Interest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $interest = Interest::create($data);

        return response()->json($interest->toArray(), 201);
    }
}
