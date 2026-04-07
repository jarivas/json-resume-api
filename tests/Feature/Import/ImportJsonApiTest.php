<?php

namespace Tests\Feature\Import;

use App\Console\Commands\ImportJsonData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImportJsonApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(ImportJsonData::JSON_RESUME_SCHEMA_CACHE_KEY);
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/import/json', [
            'file' => UploadedFile::fake()->create('resume.json', 5, 'application/json'),
        ]);

        $response->assertUnauthorized();
    }

    public function test_it_validates_file_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/import/json', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_it_imports_json_resume_via_api_endpoint(): void
    {
        Http::fake([
            ImportJsonData::JSON_RESUME_SCHEMA_URL => Http::response($this->jsonResumeSchemaFixture(), 200),
        ]);

        $user = User::factory()->create();
        $path = 'imports/api-json-'.Str::uuid().'.json';
        $tmpFile = UploadedFile::fake()->createWithContent('resume.json', $this->resumeFixtureJson());

        try {
            Storage::disk('local')->delete($path);

            $response = $this->actingAs($user)->post('/api/import/json', [
                'file' => $tmpFile,
                'disk' => 'local',
                'path' => $path,
            ], ['Accept' => 'application/json']);

            $response->assertOk();
            $response->assertJsonPath('ok', true);
            $this->assertTrue(Storage::disk('local')->exists($path));
            $this->assertDatabaseCount('basics', 1);
        } finally {
            Storage::disk('local')->delete($path);
        }
    }

    private function jsonResumeSchemaFixture(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/jsonresume-schema.json'));
    }

    private function resumeFixtureJson(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/cv.json'));
    }
}
