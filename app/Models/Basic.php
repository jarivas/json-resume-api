<?php

namespace App\Models;

use App\Helpers\Model\Location;
use App\Helpers\Model\Profile;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public $with = [
        'works',
        'volunteers',
        'educations',
        'awards',
        'certificates',
        'publications',
        'skills',
        'languages',
        'interests',
        'references',
        'projects',
    ];

    protected function casts(): array
    {
        return [
            'location' => Location::class,
            'profiles' => Profile::class,
        ];
    }

    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }

    public function volunteers(): HasMany
    {
        return $this->hasMany(Volunteer::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(Interest::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(Reference::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
