<?php

namespace App\Models;

use App\Helpers\Model\Location;
use App\Helpers\Model\Profile;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $name
 * @property string $label
 * @property string $email
 * @property string $phone
 * @property string $url
 * @property string $summary
 * @property Location $location
 * @property \Illuminate\Support\Collection<Profile> $profiles
 * @property-read \Illuminate\Database\Eloquent\Collection<Work> $works
 * @property-read \Illuminate\Database\Eloquent\Collection<Volunteer> $volunteers
 * @property-read \Illuminate\Database\Eloquent\Collection<Education> $educations
 * @property-read \Illuminate\Database\Eloquent\Collection<Award> $awards
 * @property-read \Illuminate\Database\Eloquent\Collection<Certificate> $certificates
 * @property-read \Illuminate\Database\Eloquent\Collection<Publication> $publications
 * @property-read \Illuminate\Database\Eloquent\Collection<Skill> $skills
 * @property-read \Illuminate\Database\Eloquent\Collection<Language> $languages
 * @property-read \Illuminate\Database\Eloquent\Collection<Interest> $interests
 * @property-read \Illuminate\Database\Eloquent\Collection<Reference> $references
 * @property-read \Illuminate\Database\Eloquent\Collection<Project> $projects
 */
class Basic extends Model
{
    /** @use HasFactory<\Database\Factories\BasicFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'label',
        'email',
        'phone',
        'url',
        'summary',
        'location',
        'profiles',
    ];

    protected function casts(): array
    {
        return [
            'location' => Location::class,
            'profiles' => Profile::class,
        ];
    }

    public function work(): BelongsTo
    {
        return $this->BelongsTo(Work::class);
    }

    public function volunteer(): BelongsTo
    {
        return $this->BelongsTo(Volunteer::class);
    }

    public function education(): BelongsTo
    {
        return $this->BelongsTo(Education::class);
    }

    public function awards(): BelongsTo
    {
        return $this->BelongsTo(Award::class);
    }

    public function certificates(): BelongsTo
    {
        return $this->BelongsTo(Certificate::class);
    }

    public function publications(): BelongsTo
    {
        return $this->BelongsTo(Publication::class);
    }

    public function skills(): BelongsTo
    {
        return $this->BelongsTo(Skill::class);
    }

    public function languages(): BelongsTo
    {
        return $this->BelongsTo(Language::class);
    }

    public function interests(): BelongsTo
    {
        return $this->BelongsTo(Interest::class);
    }

    public function references(): BelongsTo
    {
        return $this->BelongsTo(Reference::class);
    }

    public function projects(): BelongsTo
    {
        return $this->BelongsTo(Project::class);
    }
}
