<?php

namespace Tests\Feature\Console;

use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportJsonDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_json_from_url(): void
    {
        Storage::fake('local');
        ['path' => $fixturePath, 'content' => $fixtureContent, 'data' => $fixtureData] = $this->loadCvFixture();

        Http::fake([
            'https://example.test/resume.json' => Http::response($fixtureContent, 200),
        ]);

        $storagePath = 'imports/resume-from-url.json';

        $this->artisan('data:import', [
            'source' => 'https://example.test/resume.json',
            '--disk' => 'local',
            '--path' => $storagePath,
        ])->assertExitCode(0);

        $saved = $this->assertImportedResumeStored($storagePath, $fixtureData);

        $this->assertSame('José Antonio Rivas Fernández', data_get($saved, 'basics.name'));
        $this->assertResumeWasPersisted();
    }

    public function test_it_imports_json_from_local_file_path(): void
    {
        Storage::fake('local');
        ['path' => $fixturePath, 'content' => $fixtureContent, 'data' => $fixtureData] = $this->loadCvFixture();

        $storagePath = 'imports/cv.json';

        $this->artisan('data:import', [
            'source' => $fixturePath,
            '--disk' => 'local',
            '--path' => $storagePath,
        ])->assertExitCode(0);

        $saved = $this->assertImportedResumeStored($storagePath, $fixtureData);

        $this->assertSame('José Antonio Rivas Fernández', data_get($saved, 'basics.name'));
        $this->assertSame('InOne', data_get($saved, 'work.0.name'));
        $this->assertSame('Español', data_get($saved, 'languages.0.language'));

        $this->assertResumeWasPersisted();
    }

    public function test_it_fails_when_json_resume_format_is_invalid(): void
    {
        Storage::fake('local');
        $fixturePath = storage_path('app/test-invalid-resume.json');

        File::put($fixturePath, json_encode([
            'basics' => [
                'name' => 'Invalid User',
                'email' => 'invalid-email',
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $this->artisan('data:import', [
                'source' => $fixturePath,
            ])->assertExitCode(1);

            Storage::disk('local')->assertMissing('imports/imported-data.json');
            $this->assertDatabaseCount('basics', 0);
        } finally {
            File::delete($fixturePath);
        }
    }

    /**
     * @return array{path: string, content: string, data: array<mixed>}
     */
    private function loadCvFixture(): array
    {
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        $fixtureContent = File::get($fixturePath);

        return [
            'path' => $fixturePath,
            'content' => $fixtureContent,
            'data' => json_decode($fixtureContent, true, 512, JSON_THROW_ON_ERROR),
        ];
    }

    /**
     * @param  array<mixed>  $fixtureData
     * @return array<mixed>
     */
    private function assertImportedResumeStored(string $storagePath, array $fixtureData): array
    {
        Storage::disk('local')->assertExists($storagePath);

        $saved = json_decode(Storage::disk('local')->get($storagePath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($saved);
        $this->assertSame($fixtureData, $saved);

        return $saved;
    }

    private function assertResumeWasPersisted(): void
    {
        $this->assertDatabaseCount('basics', 1);
        $this->assertDatabaseCount('works', 6);
        $this->assertDatabaseCount('volunteers', 0);
        $this->assertDatabaseCount('educations', 2);
        $this->assertDatabaseCount('awards', 0);
        $this->assertDatabaseCount('certificates', 10);
        $this->assertDatabaseCount('publications', 0);
        $this->assertDatabaseCount('skills', 4);
        $this->assertDatabaseCount('languages', 2);
        $this->assertDatabaseCount('interests', 0);
        $this->assertDatabaseCount('references', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseHas('basics', [
            'name' => 'José Antonio Rivas Fernández',
        ]);
        $this->assertDatabaseHas('works', [
            'name' => 'InOne',
            'position' => 'Head of Development',
        ]);

        $basic = Basic::query()->firstOrFail();

        $this->assertSame('Team Lead / Head of Development', $basic->label);
        $this->assertSame('me@jarivas.work', $basic->email);
        $this->assertSame('Málaga', data_get($basic->location, 'city'));
        $this->assertCount(2, $basic->profiles);

        $this->assertCount(6, $basic->works);
        $this->assertCount(2, $basic->educations);
        $this->assertCount(10, $basic->certificates);
        $this->assertCount(4, $basic->skills);
        $this->assertCount(2, $basic->languages);
        $this->assertCount(0, $basic->volunteers);
        $this->assertCount(0, $basic->awards);
        $this->assertCount(0, $basic->publications);
        $this->assertCount(0, $basic->interests);
        $this->assertCount(0, $basic->references);
        $this->assertCount(0, $basic->projects);

        $this->assertSame('InOne', $basic->works->first()->name);
        $this->assertSame('Head of Development', $basic->works->first()->position);
        $this->assertSame('Universitat Oberta de Catalunya (UOC)', $basic->educations->first()->institution);
        $this->assertSame('Google', $basic->certificates->firstWhere('name', 'Google Data Analytics Professional Certificate')?->issuer);
        $this->assertSame('Backend Development', $basic->skills->first()->name);
        $this->assertSame('Nativo', $basic->languages->firstWhere('language', 'Español')?->fluency);
    }
}
