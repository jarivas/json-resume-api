<?php

namespace App\Helpers\Model;

use CastModels\Model;

class Location extends Model
{
    public string $address;
    public string $postalCode;
    public string $city;
    public string $countryCode;
    public string $region;
}