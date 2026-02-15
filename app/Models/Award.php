<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $title
 * @property Carbon $date
 * @property string $awarder
 * @property string $summary
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Award extends Model
{
    /** @use HasFactory<\Database\Factories\AwardFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'title',
        'date',
        'awarder',
        'summary',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(Basic::class);
    }
}
