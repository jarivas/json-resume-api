<?php

namespace App\Http\Controllers\Work;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Work\Create as Request;
use App\Models\Work;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Create extends BaseController
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $work = Work::create($data);

        return response()->json($work->toArray(), 201);
    }
}
