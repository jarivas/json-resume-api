<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property string $publisher
 * @property Carbon $releaseDate
 * @property string $url
 * @property string $summary
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Publication extends Model
{
    /** @use HasFactory<\Database\Factories\PublicationFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'name',
        'publisher',
        'releaseDate',
        'url',
        'summary',
    ];

    protected $casts = [
        'releaseDate' => 'datetime:Y-m-d',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(Basic::class);
    }
}
