<?php

namespace App\Http\Controllers\Volunteer;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\Volunteer\Update as Request;
use App\Models\Volunteer;

class Update extends BaseController
{
    public function __invoke(Request $request, Volunteer $volunteer)
    {
        $data = $request->validated();

        if (!empty($data)) {
            $volunteer->update($data);
        }

        $volunteer->update($data);

        return response()->json($volunteer->toArray());
    }
}
