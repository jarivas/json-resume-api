<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $language
 * @property string $fluency
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Language extends Model
{
    /** @use HasFactory<\Database\Factories\LanguageFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'language',
        'fluency',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(
            Basic::class,
            'basic_languages',
            'language_id',
            'basic_id'
        );
    }
}
