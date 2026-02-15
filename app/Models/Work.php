<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $name
 * @property string $position
 * @property string $url
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $summary
 * @property array $highlights
 * @property-read Basic $basic
 * @property string $basic_id
 */
class Work extends Model
{
    /** @use HasFactory<\Database\Factories\WorkFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'name',
        'position',
        'url',
        'startDate',
        'endDate',
        'summary',
        'highlights',
        'basic_id',
    ];

    protected $casts = [
        'startDate' => 'datetime:Y-m-d',
        'endDate' => 'datetime:Y-m-d',
        'highlights' => 'array'
    ];

    public function basic(): BelongsTo
    {
        return $this->belongsTo(Basic::class);
    }
}
