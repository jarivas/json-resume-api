<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $name
 * @property string $reference
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Reference extends Model
{
    /** @use HasFactory<\Database\Factories\ReferenceFactory> */
    use HasUuids, HasFactory;
    
    protected $fillable = [
        'name',
        'reference',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(Basic::class);
    }
}
