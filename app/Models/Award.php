<?php

namespace App\Models;

use Database\Factories\AwardFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property Carbon $date
 * @property string $awarder
 * @property string $summary
 */
class Award extends Model
{
    /** @use HasFactory<AwardFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'title',
        'date',
        'awarder',
        'summary',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];
}
