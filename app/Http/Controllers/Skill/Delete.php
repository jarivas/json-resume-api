<?php

namespace App\Http\Controllers\Skill;

use App\Http\Controllers\Controller;
use App\Models\Skill;

class Delete extends Controller
{
    public function __invoke(Skill $skill)
    {
        return $skill->delete() ? response()->noContent() :
            $this->getErrorResponse('error', 'Problem deleting the Skill model');
    }
}
