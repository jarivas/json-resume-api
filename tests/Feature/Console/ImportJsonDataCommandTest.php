<?php

namespace Tests\Feature\Console;

use App\Models\Basic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

class ImportJsonDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_json_from_url(): void
    {
        Embeddings::fake();
        ['path' => $fixturePath, 'content' => $fixtureContent, 'data' => $fixtureData] = $this->loadCvFixture();

        Http::fake([
            'https://example.test/resume.json' => Http::response($fixtureContent, 200),
        ]);

        $storagePath = 'imports/resume-from-url-'.Str::uuid().'.json';

        try {
            Storage::disk('local')->delete($storagePath);

            $this->artisan('data:import', [
                'source' => 'https://example.test/resume.json',
                '--disk' => 'local',
                '--path' => $storagePath,
            ])->assertExitCode(0);

            $saved = $this->assertImportedResumeStored($storagePath, $fixtureData);

            $this->assertSame('José Antonio Rivas Fernández', data_get($saved, 'basics.name'));
            $this->assertResumeWasPersisted();
            $this->assertDatabaseCount('resume_embeddings', 25);
        } finally {
            Storage::disk('local')->delete($storagePath);
        }
    }

    public function test_it_imports_json_from_local_file_path(): void
    {
        Embeddings::fake();
        ['path' => $fixturePath, 'content' => $fixtureContent, 'data' => $fixtureData] = $this->loadCvFixture();

        $storagePath = 'imports/cv-'.Str::uuid().'.json';

        try {
            Storage::disk('local')->delete($storagePath);

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
            $this->assertDatabaseCount('resume_embeddings', 25);
        } finally {
            Storage::disk('local')->delete($storagePath);
        }
    }

    public function test_it_clears_existing_local_embeddings_before_import(): void
    {
        Embeddings::fake();
        Cache::put('resume_keywords_generic', ['stale']);
        Cache::put('resume_keywords_verbs', ['stale']);

        DB::table('resume_embeddings')->insert([
            'id' => (string) Str::ulid(),
            'model_type' => Basic::class,
            'model_id' => 'legacy-basic-id',
            'content' => 'stale embedding content',
            'vector' => json_encode([0.1, 0.2, 0.3], JSON_THROW_ON_ERROR),
            'vector_length' => 3,
            'embedding_model' => 'legacy-model',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resume_keywords')->insert([
            'keyword' => 'stale-keyword-only-for-test',
            'category' => 'resume',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ['path' => $fixturePath] = $this->loadCvFixture();
        $storagePath = 'imports/cv-clean-reset-'.Str::uuid().'.json';

        try {
            Storage::disk('local')->delete($storagePath);

            $this->artisan('data:import', [
                'source' => $fixturePath,
                '--disk' => 'local',
                '--path' => $storagePath,
            ])->assertExitCode(0);

            $this->assertDatabaseCount('resume_embeddings', 25);
            $this->assertDatabaseMissing('resume_embeddings', [
                'content' => 'stale embedding content',
            ]);
            $this->assertDatabaseMissing('resume_keywords', [
                'keyword' => 'stale-keyword-only-for-test',
            ]);
            $this->assertFalse(Cache::has('resume_keywords_generic'));
            $this->assertFalse(Cache::has('resume_keywords_verbs'));
        } finally {
            Storage::disk('local')->delete($storagePath);
        }
    }

    public function test_it_fails_when_json_resume_format_is_invalid(): void
    {
        $fixturePath = storage_path('app/test-invalid-resume.json');

        File::put($fixturePath, json_encode([
            'basics' => [
                'name' => 'Invalid User',
                'email' => 'invalid-email',
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            Storage::disk('local')->delete('imports/imported-data.json');

            $this->artisan('data:import', [
                'source' => $fixturePath,
            ])->assertExitCode(1);

            Storage::disk('local')->assertMissing('imports/imported-data.json');
            $this->assertDatabaseCount('basics', 0);
        } finally {
            File::delete($fixturePath);
            Storage::disk('local')->delete('imports/imported-data.json');
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
    }
}
