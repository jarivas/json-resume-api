<?php

namespace App\Http\Controllers\Iso;

use Io238\ISOCountries\Models\Country as Model;

class Country
{
    public function __invoke()
    {
        return Model::all();
    }
}