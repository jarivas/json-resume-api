<?php

namespace App\Models;

use Database\Factories\VolunteerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $organization
 * @property string $position
 * @property string $url
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $summary
 * @property array $highlights
 */
class Volunteer extends Model
{
    /** @use HasFactory<VolunteerFactory> */
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
}
