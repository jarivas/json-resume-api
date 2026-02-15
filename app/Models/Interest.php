<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property array $keywords
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Interest extends Model
{
    /** @use HasFactory<\Database\Factories\InterestFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'name',
        'keywords',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(Basic::class);
    }

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
        ];
    }
}
