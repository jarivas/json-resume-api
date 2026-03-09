<?php

namespace App\Http\Controllers\Certificate;

use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\Certificate\Create as Request;
use App\Models\Certificate;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $certificate = Certificate::create($data);

        return response()->json($certificate->toArray(), 201);
    }
}
