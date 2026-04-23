<?php

namespace Tests\Feature\Import;

use App\Ai\Agents\ResumeImportAgent;
use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files;
use Tests\TestCase;

class EducationImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_skills_and_attaches_to_education(): void
    {
        Storage::fake('local');
        Files::fake();

        $user = User::factory()->create();

        // Fake ResumeImportAgent response: one education and two structured skills
        $resumeResponse = json_encode([
            'educations' => [
                ['institution' => 'Example University', 'area' => 'Test', 'studyType' => 'Course', 'startDate' => '2020-01-01', 'endDate' => '2020-06-01'],
            ],
            'skills' => [
                ['name' => 'Laravel', 'confidence' => 0.98, 'keywords' => ['Eloquent', 'Artisan']],
                ['name' => 'Docker', 'confidence' => 0.75],
            ],
        ]);

        ResumeImportAgent::fake([$resumeResponse]);

        // Use a real fixture PDF file to exercise the extractor
        $fixture = base_path('tests/Feature/Import/Fixtures/education - desarrollo-de-aplicaciones-informaticas-ingles.pdf');
        $this->assertFileExists($fixture);

        $file = new UploadedFile($fixture, basename($fixture), null, null, true);

        $res = $this->actingAs($user)->postJson('/api/import/education', [
            'file' => $file,
        ]);

        $res->assertOk();
        $res->assertJsonPath('ok', true);
        $res->assertJsonCount(2, 'skills');

        $this->assertDatabaseHas('skills', ['name' => 'laravel']);
        $this->assertDatabaseHas('skills', ['name' => 'docker']);

        $created = Education::orderBy('created_at', 'desc')->first();
        $this->assertNotNull($created);
        $this->assertDatabaseHas('education_skill', ['education_id' => $created->id]);
    }
}
