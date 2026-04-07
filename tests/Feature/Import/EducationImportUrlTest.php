<?php

namespace Tests\Feature\Import;

use App\Ai\Agents\SkillExtractionAgent;
use App\Models\Education;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files;
use Tests\TestCase;

class EducationImportUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_from_url_and_attaches_skills(): void
    {
        Storage::fake('local');
        Files::fake();

        $user = User::factory()->create();
        $education = Education::factory()->create();

        $url = 'https://example.com/page-with-skills';

        Http::fake([$url => Http::response('<html>Laravel, Docker</html>', 200, ['Content-Type' => 'text/html'])]);

        $response = json_encode([
            ['name' => 'Laravel', 'confidence' => 0.99],
        ]);

        SkillExtractionAgent::fake([$response]);

        $res = $this->actingAs($user)->postJson('/api/import/education', [
            'url' => $url,
            'education_id' => $education->id,
        ]);

        $res->assertOk();
        $res->assertJsonPath('ok', true);
        $res->assertJsonCount(1, 'skills');

        $this->assertDatabaseHas('skills', ['name' => 'laravel']);
        $this->assertDatabaseHas('education_skill', ['education_id' => $education->id]);
    }
}
