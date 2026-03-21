<?php

namespace Tests\Feature\Embedding;

use App\Services\Ai\EmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmbeddingSimilarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_similarity_score_high_for_relevant_query_and_low_for_irrelevant(): void
    {
        Storage::fake('local');

        // Import fixture to populate resume_embeddings
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        $this->artisan('data:import', ['source' => $fixturePath, '--disk' => 'local', '--path' => 'imports/cv.json'])->assertExitCode(0);

        $svc = new EmbeddingService();

        $topRelevant = $svc->findMostSimilar('PHP Laravel', 1);
        $this->assertNotEmpty($topRelevant, 'Expected at least one result for relevant query');
        $this->assertGreaterThan(0.02, $topRelevant[0]['score'], 'Expected a non-trivial similarity for relevant query');

        $topIrrelevant = $svc->findMostSimilar('Salsa dancing choreography', 1);
        if (! empty($topIrrelevant)) {
            $this->assertLessThan(0.02, $topIrrelevant[0]['score'], 'Expected low similarity for irrelevant query');
        } else {
            $this->assertEmpty($topIrrelevant);
        }
    }
}
