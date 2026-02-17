<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'reference',
    ];

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(
            Basic::class,
            'basic_references',
            'reference_id',
            'basic_id'
        );
    }
}
