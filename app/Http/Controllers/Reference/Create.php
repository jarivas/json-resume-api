<?php

namespace App\Http\Controllers\Reference;

use App\Http\Requests\Reference\Create as Request;
use App\Models\Reference;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $reference = Reference::create($data);

        return response()->json($reference->toArray(), 201);
    }
}
