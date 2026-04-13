<?php

namespace Tests\Feature\Import;

use App\Ai\Agents\ResumeImportAgent;
use App\Ai\Agents\SkillExtractionAgent;
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

        // Fake agent response: an array with two skills
        $response = json_encode([
            ['name' => 'Laravel', 'confidence' => 0.98, 'keywords' => ['Eloquent', 'Artisan']],
            ['name' => 'Docker', 'confidence' => 0.75],
        ]);

        SkillExtractionAgent::fake([$response]);

        // Use fixture content to create Education (no ResumeImportAgent fake)

        $file = UploadedFile::fake()->createWithContent('doc.pdf', 'PDF-BINARY');

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
