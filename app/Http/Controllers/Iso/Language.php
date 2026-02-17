<?php

namespace App\Http\Controllers\Iso;

use Io238\ISOCountries\Models\Language as Model;

class Language
{
    public function __invoke()
    {
        return Model::all();
    }
}
