<?php

namespace App\Http\Controllers\Language;

use App\Http\Controllers\Controller;
use App\Models\Basic;
use App\Models\Language;

class Detach extends Controller
{
    public function __invoke(Language $language, Basic $basic)
    {
        $basic->languages()->detach($language);

        return response()->noContent();
    }
}
