<?php

namespace App\Http\Controllers\Basic;

use App\Models\Basic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Basic\Create as Request;
use Illuminate\Support\Facades\Log;

class Create extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validated();

        $model = Basic::create($data);

        Log::info('basic.create.response', $model->toArray());

        return response()->json($model->toArray(), 201);
    }
}