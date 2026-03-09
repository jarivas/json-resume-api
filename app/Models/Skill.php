<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $name
 * @property string $level
 * @property array $keywords
 * @property string $basic_id
 * @property-read Basic $basic
 */
class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'level',
        'keywords',
        'basic_id',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function basic(): BelongsTo
    {
        return $this->belongsTo(
            Basic::class,
        );
    }
}
