<?php

namespace Tests\Feature\Basic;

use App\Models\Award;
use App\Models\Basic;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Reference;
use App\Models\Skill;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ReadOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_read_one_ok()
    {
        $user = User::factory()->create();
        $basic = Basic::factory()->create();
        $location = $basic->location;
        $profile = $basic->profiles->first();

        $work = Work::factory()->create();
        $basic->work()->attach($work);

        $volunteer = Volunteer::factory()->create();
        $basic->volunteer()->attach($volunteer);

        $education = Education::factory()->create();
        $basic->education()->attach($education);

        $award = Award::factory()->create();
        $basic->awards()->attach($award);

        $certificate = Certificate::factory()->create();
        $basic->certificates()->attach($certificate);

        $publication = Publication::factory()->create();
        $basic->publications()->attach($publication);

        $skill = Skill::factory()->create();
        $basic->skills()->attach($skill);

        $language = Language::factory()->create();
        $basic->languages()->attach($language);

        $interest = Interest::factory()->create();
        $basic->interests()->attach($interest);

        $reference = Reference::factory()->create();
        $basic->references()->attach($reference);

        $project = Project::factory()->create();
        $basic->projects()->attach($project);

        $url = "/api/basic/{$basic->id}";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json->has('id')
                ->where('name', $basic->name)
                ->where('label', $basic->label)
                ->where('email', $basic->email)
                ->where('phone', $basic->phone)
                ->where('url', $basic->url)
                ->where('summary', $basic->summary)
                ->has('location', fn (AssertableJson $json) => $json->where('address', $location->address)
                    ->where('postalCode', $location->postalCode)
                    ->where('city', $location->city)
                    ->where('countryCode', $location->countryCode))
                ->has('profiles', 1, fn (AssertableJson $json) => $json->where('network', $profile->network)
                    ->where('username', $profile->username)
                    ->where('url', $profile->url)
                )
                ->has('work', 1, fn (AssertableJson $json) => $json->where('name', $work->name)
                    ->where('position', $work->position)
                    ->where('url', $work->url)
                    ->where('startDate', $work->startDate->format('Y-m-d'))
                    ->where('endDate', $work->endDate->format('Y-m-d'))
                    ->where('summary', $work->summary)
                    ->etc()
                )
                ->has('volunteer', 1, fn (AssertableJson $json) => $json->where('organization', $volunteer->organization)
                    ->where('position', $volunteer->position)
                    ->where('url', $volunteer->url)
                    ->where('startDate', $volunteer->startDate->format('Y-m-d'))
                    ->where('endDate', $volunteer->endDate->format('Y-m-d'))
                    ->where('summary', $volunteer->summary)
                    ->etc()
                )
                ->has('education', 1, fn (AssertableJson $json) => $json->where('institution', $education->institution)
                    ->where('url', $education->url)
                    ->where('area', $education->area)
                    ->where('studyType', $education->studyType)
                    ->where('startDate', $education->startDate->format('Y-m-d'))
                    ->where('endDate', $education->endDate->format('Y-m-d'))
                    ->where('score', $education->score)
                    ->where('summary', $education->summary)
                    ->where('courses', $education->courses)
                    ->etc()
                )
                ->has('awards', 1, fn (AssertableJson $json) => $json->where('title', $award->title)
                    ->where('date', $award->date->format('Y-m-d'))
                    ->where('awarder', $award->awarder)
                    ->where('summary', $award->summary)
                    ->etc()
                )
                ->has('certificates', 1, fn (AssertableJson $json) => $json->where('name', $certificate->name)
                    ->where('date', $certificate->date->format('Y-m-d'))
                    ->where('issuer', $certificate->issuer)
                    ->where('url', $certificate->url)
                    ->etc()
                )
                ->has('publications', 1, fn (AssertableJson $json) => $json->where('name', $publication->name)
                    ->where('publisher', $publication->publisher)
                    ->where('releaseDate', $publication->releaseDate->format('Y-m-d'))
                    ->where('url', $publication->url)
                    ->where('summary', $publication->summary)
                    ->etc()
                )
                ->has('skills', 1, fn (AssertableJson $json) => $json->where('name', $skill->name)
                    ->where('level', $skill->level)
                    ->where('keywords', $skill->keywords)
                    ->etc()
                )
                ->has('languages', 1, fn (AssertableJson $json) => $json->where('language', $language->language)
                    ->where('fluency', $language->fluency)
                    ->etc()
                )
                ->has('interests', 1, fn (AssertableJson $json) => $json->where('name', $interest->name)
                    ->where('keywords', $interest->keywords)
                    ->etc()
                )
                ->has('references', 1, fn (AssertableJson $json) => $json->where('name', $reference->name)
                    ->where('reference', $reference->reference)
                    ->etc()
                )
                ->has('projects', 1, fn (AssertableJson $json) => $json->where('name', $project->name)
                    ->where('description', $project->description)
                    ->where('url', $project->url)
                    ->where('startDate', $project->startDate->format('Y-m-d'))
                    ->where('endDate', $project->endDate->format('Y-m-d'))
                    ->etc()
                )  
                ->etc()
        );
    }

    public function test_basic_read_one_not_found()
    {
        $user = User::factory()->create();
        $url = "/api/basic/999";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertNotFound();
    }

    public function test_basic_read_one_not_found_ulid()
    {
        $user = User::factory()->create();
        $ulid = '01FZ1GZ5K9XQZ7Z8X9Y0A1B2C3';
        $url = "/api/basic/{$ulid}";
        $response = $this->actingAs($user)->getJson($url);
        $response->assertNotFound();
    } 

    public function test_basic_read_one_unauthenticated()
    {
        $basic = Basic::factory()->create();
        $url = "/api/basic/{$basic->id}";
        $response = $this->getJson($url);
        $response->assertUnauthorized();
    }
}