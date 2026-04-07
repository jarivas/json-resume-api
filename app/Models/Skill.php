<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property string $level
 * @property array $keywords
 */
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'level',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function educations(): BelongsToMany
    {
        return $this->belongsToMany(Education::class, 'education_skill');
    }

    public function certificates(): BelongsToMany
    {
        return $this->belongsToMany(Certificate::class, 'certificate_skill');
    }
}
