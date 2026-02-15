<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property string $level
 * @property array $keywords
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'name',
        'level',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(Basic::class);
    }
}
