<?php

namespace App\Models;

use App\Helpers\Model\Profile;
use App\Helpers\Model\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $label
 * @property string $email
 * @property string $phone
 * @property string $url
 * @property string $summary
 * @property Location $location
 * @property Profile[] $profiles
 * @property-read \Illuminate\Support\Collection<Work> $works
 */
class Basic extends Model
{
    /** @use HasFactory<\Database\Factories\BasicFactory> */
    use HasUuids, HasFactory;
    
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

    protected $casts = [
        'location' => Location::class,
        'profiles' => Profile::class,
    ];

    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }
}
