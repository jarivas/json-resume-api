<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $publisher
 * @property Carbon $releaseDate
 * @property string $url
 * @property string $summary
 * @property string $basic_id
 * @property-read Basic $basic
 */
class Publication extends Model
{
    /** @use HasFactory<\Database\Factories\PublicationFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'publisher',
        'releaseDate',
        'url',
        'summary',
        'basic_id',
    ];

    protected $casts = [
        'releaseDate' => 'datetime:Y-m-d',
    ];

    public function basic(): BelongsTo
    {
        return $this->belongsTo(
            Basic::class,
        );
    }
}
