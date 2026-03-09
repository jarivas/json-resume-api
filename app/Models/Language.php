<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $language
 * @property string $fluency
 * @property string $basic_id
 * @property-read Basic $basic
 */
class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'language',
        'fluency',
        'basic_id',
    ];

    public function basic(): BelongsTo
    {
        return $this->belongsTo(
            Basic::class,
        );
    }
}
