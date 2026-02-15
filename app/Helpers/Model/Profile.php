<?php

namespace App\Helpers\Model;

use CastModels\Model;

class Profile extends Model
{
    public string $network;
    public string $username;
    public string $url;
}