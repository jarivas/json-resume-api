<?php

namespace App\Http\Controllers\Volunteer;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Volunteer\Create as Request;
use App\Models\Volunteer;

use Symfony\Component\HttpKernel\Exception\HttpException;
class Create extends BaseController
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $volunteer = Volunteer::create($data);

        return response()->json($volunteer->toArray(), 201);
    }
}
