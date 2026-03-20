<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Requests\Volunteer\Update as Request;
use App\Models\Volunteer;
use Illuminate\Routing\Controller as BaseController;

class Update extends BaseController
{
    public function __invoke(Request $request, Volunteer $volunteer)
    {
        $data = $request->validated();

        if (! empty($data)) {
            $volunteer->update($data);
        }

        $volunteer->update($data);

        return response()->json($volunteer->toArray());
    }
}
