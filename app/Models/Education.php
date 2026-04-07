<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $institution
 * @property string $url
 * @property string $area
 * @property string $studyType
 * @property Carbon $startDate
 * @property Carbon $endDate
 * @property string $score
 * @property string $summary
 * @property array $courses
 */
class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory, HasUlids;

    protected $table = 'educations';

    protected $fillable = [
        'institution',
        'url',
        'area',
        'studyType',
        'startDate',
        'endDate',
        'score',
        'summary',
        'courses',
    ];

    protected $casts = [
        'startDate' => 'datetime:Y-m-d',
        'endDate' => 'datetime:Y-m-d',
        'courses' => 'array',
    ];

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'education_skill');
    }
}
