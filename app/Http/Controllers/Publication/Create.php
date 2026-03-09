<?php

namespace App\Http\Controllers\Publication;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Publication\Create as Request;
use App\Models\Publication;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $publication = Publication::create($data);

        return response()->json($publication->toArray(), 201);
    }
}
