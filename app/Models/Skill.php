<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $level
 * @property array $keywords
 */
class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'level',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];
}
