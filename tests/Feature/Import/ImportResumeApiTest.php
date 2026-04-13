<?php

namespace Tests\Feature\Import;

use App\Ai\Agents\ResumeImportAgent;
use App\Console\Commands\ImportJsonData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files;
use Tests\TestCase;

class ImportResumeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(ImportJsonData::JSON_RESUME_SCHEMA_CACHE_KEY);
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/import/resume', [
            'file' => UploadedFile::fake()->create('resume.pdf', 50, 'application/pdf'),
        ]);

        $response->assertUnauthorized();
    }

    public function test_it_validates_file_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/import/resume', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_it_imports_resume_document_via_api_endpoint(): void
    {
        Http::fake([
            ImportJsonData::JSON_RESUME_SCHEMA_URL => Http::response($this->jsonResumeSchemaFixture(), 200),
        ]);

        ResumeImportAgent::fake([$this->loadIntermediateFixture()]);

        $user = User::factory()->create();
        $tmpFile = UploadedFile::fake()->createWithContent('resume.pdf', $this->resumePdfFixtureBinary());

        Files::fake();

        try {
            Storage::disk('local')->delete('imports/imported-data.json');

            $response = $this->actingAs($user)->post('/api/import/resume', [
                'file' => $tmpFile,
            ], ['Accept' => 'application/json']);

            $response->assertOk();
            $response->assertJsonPath('ok', true);
            $this->assertDatabaseCount('basics', 1);
        } finally {
            Storage::disk('local')->delete('imports/imported-data.json');
        }
    }

    private function loadIntermediateFixture(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/cv-intermediate.json'));
    }

    private function jsonResumeSchemaFixture(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/jsonresume-schema.json'));
    }

    private function resumePdfFixtureBinary(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/Curriculum vitae.pdf'));
    }
}
