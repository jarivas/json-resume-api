<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $position
 * @property string $url
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $summary
 * @property array $highlights
 */
class Work extends Model
{
    /** @use HasFactory<\Database\Factories\WorkFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
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
