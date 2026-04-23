<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Resources\BasicResource;

class Read
{
    public function __invoke()
    {
        $basic = Basic::first();

        return BasicResource::collection(collect([$basic])->filter());
    }
}
