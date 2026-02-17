<?php

namespace App\Http\Controllers\Iso;

use Io238\ISOCountries\Models\Currency as Model;

class Currency
{
    public function __invoke()
    {
        return Model::all();
    }
}
