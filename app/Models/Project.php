<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $description
 * @property array $highlights
 * @property string $url
 * @property-read \Illuminate\Support\Collection<Basic> $basics
 */
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'startDate',
        'endDate',
        'description',
        'highlights',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'startDate' => 'datetime:Y-m-d',
            'endDate' => 'datetime:Y-m-d',
            'highlights' => 'array',
        ];
    }

    public function basics(): BelongsToMany
    {
        return $this->belongsToMany(
            Basic::class,
            'basic_projects',
            'project_id',
            'basic_id'
        );
    }
}
