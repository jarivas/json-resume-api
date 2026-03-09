<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property Carbon $date
 * @property string $awarder
 * @property string $summary
 * @property string $basic_id
 * @property-read Basic $basic
 */
class Award extends Model
{
    /** @use HasFactory<\Database\Factories\AwardFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'title',
        'date',
        'awarder',
        'summary',
        'basic_id',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function basic(): BelongsTo
    {
        return $this->belongsTo(
            Basic::class,
        );
    }
}
