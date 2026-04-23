<?php

namespace App\Http\Controllers\AiRequest;

use App\Models\AiRequest;

class Read
{
    public function __invoke(int $page = 1)
    {
        $items = AiRequest::latest()->paginate(15, ['*'], 'page', $page);

        return response()->json($items);
    }
}
