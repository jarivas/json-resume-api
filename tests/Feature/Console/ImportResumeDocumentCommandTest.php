<?php

namespace Tests\Feature\Console;

use App\Ai\Agents\ResumeImportAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

class ImportResumeDocumentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_a_local_pdf_document(): void
    {
        Embeddings::fake();
        $fixture = $this->loadCvFixture();
        ResumeImportAgent::fake([$fixture['content']]);

        $documentPath = base_path('tests/Feature/Console/Fixtures/Curriculum vitae.pdf');
        $generatedPath = 'imports/extracted-pdf-'.Str::uuid().'.json';

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
                '--disk' => 'local',
                '--path' => $generatedPath,
            ])->assertExitCode(0);

            $this->assertFalse(Storage::disk('local')->exists($generatedPath));
            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('basics', [
                'name' => 'José Antonio Rivas Fernández',
            ]);
            $this->assertDatabaseCount('works', 6);
            $this->assertDatabaseCount('educations', 2);
            $this->assertDatabaseCount('certificates', 10);
            $this->assertDatabaseCount('skills', 4);
            $this->assertDatabaseCount('languages', 2);
            $this->assertDatabaseHas('works', [
                'name' => 'InOne',
                'position' => 'Head of Development',
            ]);
            $this->assertDatabaseHas('educations', [
                'institution' => 'Universitat Oberta de Catalunya (UOC)',
            ]);
            $this->assertDatabaseHas('skills', [
                'name' => 'Backend Development',
            ]);
        } finally {
            Storage::disk('local')->delete($generatedPath);
        }
    }

    public function test_pdf_fixture_is_not_truncated(): void
    {
        $documentPath = base_path('tests/Feature/Console/Fixtures/Curriculum vitae.pdf');
        $bytes = File::size($documentPath);
        $header = File::get($documentPath, true);

        $this->assertGreaterThan(10_000, $bytes);
        $this->assertTrue(str_starts_with($header, '%PDF-'));
    }

    public function test_it_imports_a_local_docx_document(): void
    {
        Embeddings::fake();
        $fixture = $this->loadCvFixture();
        ResumeImportAgent::fake([$fixture['content']]);

        $documentPath = storage_path('app/test-resume-'.Str::uuid().'.docx');
        $generatedPath = 'imports/extracted-docx-'.Str::uuid().'.json';
        File::put($documentPath, 'fake-docx-content');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
                '--disk' => 'local',
                '--path' => $generatedPath,
            ])->assertExitCode(0);

            $this->assertFalse(Storage::disk('local')->exists($generatedPath));
            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('works', [
                'name' => 'InOne',
                'position' => 'Head of Development',
            ]);
        } finally {
            File::delete($documentPath);
            Storage::disk('local')->delete($generatedPath);
        }
    }

    public function test_it_imports_a_public_google_doc(): void
    {
        Embeddings::fake();
        $fixture = $this->loadCvFixture();
        ResumeImportAgent::fake([$fixture['content']]);

        Http::fake([
            'https://docs.google.com/document/d/*/export?format=txt' => Http::response('Contenido del CV', 200),
        ]);

        $generatedPath = 'imports/extracted-gdoc-'.Str::uuid().'.json';
        $source = 'https://docs.google.com/document/d/demo-doc-id/edit';

        try {
            $this->artisan('data:import-resume', [
                'source' => $source,
                '--disk' => 'local',
                '--path' => $generatedPath,
            ])->assertExitCode(0);

            $this->assertFalse(Storage::disk('local')->exists($generatedPath));
            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('languages', [
                'language' => 'Español',
            ]);
        } finally {
            Storage::disk('local')->delete($generatedPath);
        }
    }

    public function test_it_keeps_generated_json_when_keep_json_option_is_used(): void
    {
        Embeddings::fake();
        $fixture = $this->loadCvFixture();
        ResumeImportAgent::fake([$fixture['content']]);

        $documentPath = storage_path('app/test-resume-'.Str::uuid().'.pdf');
        $generatedPath = 'imports/extracted-keep-'.Str::uuid().'.json';
        File::put($documentPath, 'fake-pdf-content');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
                '--disk' => 'local',
                '--path' => $generatedPath,
                '--keep-json' => true,
            ])->assertExitCode(0);

            $this->assertTrue(Storage::disk('local')->exists($generatedPath));
            $saved = json_decode(Storage::disk('local')->get($generatedPath), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('José Antonio Rivas Fernández', data_get($saved, 'basics.name'));
            $this->assertDatabaseCount('basics', 1);
        } finally {
            File::delete($documentPath);
            Storage::disk('local')->delete($generatedPath);
        }
    }

    public function test_it_fails_for_unsupported_document_format(): void
    {
        ResumeImportAgent::fake();

        $documentPath = storage_path('app/test-resume-'.Str::uuid().'.txt');
        File::put($documentPath, 'not supported');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
            ])->assertExitCode(1);

            $this->assertDatabaseCount('basics', 0);
        } finally {
            File::delete($documentPath);
        }
    }

    /**
     * @return array{content: string, data: array<mixed>}
     */
    private function loadCvFixture(): array
    {
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        $fixtureContent = File::get($fixturePath);

        return [
            'content' => $fixtureContent,
            'data' => json_decode($fixtureContent, true, 512, JSON_THROW_ON_ERROR),
        ];
    }
}
