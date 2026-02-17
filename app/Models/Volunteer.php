<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $organization
 * @property string $position
 * @property string $url
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $summary
 * @property string $highlights
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Volunteer extends Model
{
    /** @use HasFactory<\Database\Factories\VolunteerFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'organization',
        'position',
        'url',
        'startDate',
        'endDate',
        'summary',
        'highlights',
    ];

    protected $casts = [
        'startDate' => 'datetime:Y-m-d',
        'endDate' => 'datetime:Y-m-d',
        'highlights' => 'array',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(
            Basic::class,
            'basic_volunteers',
            'volunteer_id',
            'basic_id'
        );
    }
}
