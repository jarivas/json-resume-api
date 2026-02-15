<?php

namespace App\Models;

use App\Helpers\Model\Location;
use App\Helpers\Model\Profile;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function work(): BelongsToMany
    {
        return $this->belongsToMany(Work::class, 'basic_works', 'basic_id', 'work_id');
    }

    public function volunteer(): BelongsToMany
    {
        return $this->belongsToMany(Volunteer::class, 'basic_volunteers', 'basic_id', 'volunteer_id');
    }

    public function education(): BelongsToMany
    {
        return $this->belongsToMany(Education::class, 'basic_educations', 'basic_id', 'education_id');
    }

    public function awards(): BelongsToMany
    {
        return $this->belongsToMany(Award::class, 'basic_awards', 'basic_id', 'award_id');
    }

    public function certificates(): BelongsToMany
    {
        return $this->belongsToMany(Certificate::class, 'basic_certificates', 'basic_id', 'certificate_id');
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class, 'basic_publications', 'basic_id', 'publication_id');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'basic_skills', 'basic_id', 'skill_id');
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'basic_languages', 'basic_id', 'language_id');
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'basic_interests', 'basic_id', 'interest_id');
    }

    public function references(): BelongsToMany
    {
        return $this->belongsToMany(Reference::class, 'basic_references', 'basic_id', 'reference_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'basic_projects', 'basic_id', 'project_id');
    }
}
