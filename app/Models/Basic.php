<?php

namespace App\Models;

use App\Helpers\Model\Location;
use App\Helpers\Model\Profile;
use Database\Factories\BasicFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $name
 * @property string $label
 * @property string $email
 * @property string $phone
 * @property string $url
 * @property string $summary
 * @property Location $location
 * @property Collection<Profile> $profiles
 */
class Basic extends Model
{
    /** @use HasFactory<BasicFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'label',
        'email',
        'phone',
        'url',
        'summary',
        'location',
        'profiles',
    ];

    protected function casts(): array
    {
        return [
            'location' => Location::class,
            'profiles' => Profile::class,
        ];
    }
}
