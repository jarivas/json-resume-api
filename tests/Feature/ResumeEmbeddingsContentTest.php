<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResumeEmbeddingsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_embeddings_content_is_useful_and_sanitized(): void
    {
        // Ensure deterministic local DB embeddings regardless of provider config
        config([
            'ai.default_for_embeddings' => 'openai',
            'ai.providers.openai.embedding_deployment' => 'database',
        ]);

        // Import the fixture using the command
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');

        $this->artisan('data:import', [
            'source' => $fixturePath,
            '--disk' => 'local',
            '--path' => 'imports/cv.json',
        ])->assertExitCode(0);

        $rows = DB::table('resume_embeddings')->get()->keyBy('model_type');

        // Basic should exist and include the full name, but must not include raw email or phone
        $basic = $rows->get('App\\Models\\Basic');
        $this->assertNotNull($basic, 'Basic embedding missing');
        $this->assertStringContainsString('José Antonio Rivas Fernández', $basic->content);
        $this->assertStringNotContainsString('@', $basic->content, 'Basic content contains an email');
        $this->assertStringNotContainsString('655010958', $basic->content, 'Basic content contains a phone number');

        // Work: check one job contains company and position
        $work = DB::table('resume_embeddings')->where('model_type', 'App\\Models\\Work')->first();
        $this->assertNotNull($work, 'Work embedding missing');
        $this->assertStringContainsString('InOne', $work->content);
        $this->assertStringContainsString('Head of Development', $work->content);

        // Certificate: ensure certificates entries include certificate names
        $cert = DB::table('resume_embeddings')->where('model_type', 'App\\Models\\Certificate')->first();
        $this->assertNotNull($cert, 'Certificate embedding missing');
        $this->assertStringContainsString('IFCD99', $cert->content);

        // Skill summaries should include technical keywords so semantic retrieval can find stack details.
        $skill = DB::table('resume_embeddings')->where('model_type', 'App\\Models\\Skill')->first();
        $this->assertNotNull($skill, 'Skill embedding missing');
        $this->assertStringContainsString('Backend Development', $skill->content);
        $this->assertStringContainsString('PHP 8', $skill->content);
        $this->assertStringContainsString('Laravel', $skill->content);
    }
}
