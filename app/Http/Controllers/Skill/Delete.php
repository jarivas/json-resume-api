<?php

namespace App\Http\Controllers\Skill;

use App\Models\Skill;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Delete
{
    public function __invoke(Skill $skill)
    {
        if (! $skill->delete()) {
            throw new HttpException(400, 'Problem deleting the Skill model');
        }

        return response()->noContent();
    }
}
