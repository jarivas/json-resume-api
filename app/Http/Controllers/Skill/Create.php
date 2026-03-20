<?php

namespace App\Http\Controllers\Skill;

use App\Http\Requests\Skill\Create as Request;
use App\Models\Skill;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Create
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        if (empty($data)) {
            throw new HttpException(400, 'No data provided');
        }

        $skill = Skill::create($data);

        return response()->json($skill->toArray(), 201);
    }
}
