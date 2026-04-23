<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Resources\BasicResource;

class ReadOne
{
    public function __invoke(Basic $basic)
    {
        return new BasicResource($basic);
    }
}
