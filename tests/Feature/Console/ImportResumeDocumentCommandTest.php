<?php

namespace Tests\Feature\Console;

use App\Ai\Agents\ResumeImportAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImportResumeDocumentCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * This test runs against Ollama (no fake) to verify the real model produces
     * a valid JSON Resume that the normalizer can import without errors.
     * Assertions are intentionally loose (at-least-N) because the model output
     * is non-deterministic.
     */
    public function test_it_imports_a_local_pdf_document(): void
    {
        $documentPath = base_path('tests/Feature/Console/Fixtures/Curriculum vitae.pdf');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
            ])->assertExitCode(0);

            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('basics', ['name' => 'José Antonio Rivas Fernández']);
            $this->assertGreaterThanOrEqual(1, $this->getTableCount('works'));
        } finally {
            Storage::disk('local')->delete('imports/imported-data.json');
        }
    }

    public function test_it_imports_a_local_pdf_document_with_faked_agent(): void
    {
        ResumeImportAgent::fake([$this->loadIntermediateFixture()]);

        $documentPath = base_path('tests/Feature/Console/Fixtures/Curriculum vitae.pdf');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
            ])->assertExitCode(0);

            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('basics', ['name' => 'José Antonio Rivas Fernández']);
            $this->assertDatabaseHas('works', ['name' => 'InOne']);
            $this->assertGreaterThanOrEqual(1, $this->getTableCount('works'));
        } finally {
            Storage::disk('local')->delete('imports/imported-data.json');
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
        ResumeImportAgent::fake([$this->loadIntermediateFixture()]);

        $documentPath = storage_path('app/test-resume-'.Str::uuid().'.docx');
        File::put($documentPath, 'fake-docx-content');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
            ])->assertExitCode(0);

            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('works', [
                'name' => 'InOne',
                'position' => 'Head of Development',
            ]);
        } finally {
            File::delete($documentPath);
            Storage::disk('local')->delete('imports/imported-data.json');
        }
    }

    public function test_it_imports_a_public_google_doc(): void
    {
        ResumeImportAgent::fake([$this->loadIntermediateFixture()]);

        Http::fake([
            'https://docs.google.com/document/d/*/export?format=txt' => Http::response('Contenido del CV', 200),
        ]);

        $source = 'https://docs.google.com/document/d/demo-doc-id/edit';

        try {
            $this->artisan('data:import-resume', [
                'source' => $source,
            ])->assertExitCode(0);

            $this->assertDatabaseCount('basics', 1);
            $this->assertDatabaseHas('languages', [
                'language' => 'Español',
            ]);
        } finally {
            Storage::disk('local')->delete('imports/imported-data.json');
        }
    }

    public function test_it_keeps_generated_json_when_keep_json_option_is_used(): void
    {
        ResumeImportAgent::fake([$this->loadIntermediateFixture()]);

        $documentPath = storage_path('app/test-resume-'.Str::uuid().'.pdf');
        File::put($documentPath, 'fake-pdf-content');

        try {
            $this->artisan('data:import-resume', [
                'source' => $documentPath,
            ])->assertExitCode(0);

            $this->assertTrue(Storage::disk('local')->exists('imports/imported-data.json'));
            $saved = json_decode(Storage::disk('local')->get('imports/imported-data.json'), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('José Antonio Rivas Fernández', data_get($saved, 'basics.name'));
            $this->assertDatabaseCount('basics', 1);
        } finally {
            File::delete($documentPath);
            Storage::disk('local')->delete('imports/imported-data.json');
        }
    }

    public function test_it_fails_for_unsupported_document_format(): void
    {
        // Intentionally not faking the ResumeImportAgent to exercise the real provider.

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

    private function loadIntermediateFixture(): string
    {
        return File::get(base_path('tests/Feature/Console/Fixtures/cv-intermediate.json'));
    }

    private function getTableCount(string $table): int
    {
        return (int) DB::table($table)->count();
    }
}
