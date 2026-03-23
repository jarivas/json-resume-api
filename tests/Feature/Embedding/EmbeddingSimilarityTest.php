<?php

namespace Tests\Feature\Embedding;

use App\Models\Work;
use App\Services\Ai\EmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmbeddingSimilarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_similarity_score_high_for_relevant_query_and_low_for_irrelevant(): void
    {
        // Ensure deterministic local DB embeddings regardless of provider config
        config([
            'ai.default_for_embeddings' => 'openai',
            'ai.providers.openai.embedding_deployment' => 'database',
        ]);

        Storage::fake('local');

        // Import fixture to populate resume_embeddings
        $fixturePath = base_path('tests/Feature/Console/Fixtures/cv.json');
        $this->artisan('data:import', ['source' => $fixturePath, '--disk' => 'local', '--path' => 'imports/cv.json'])->assertExitCode(0);

        $svc = new EmbeddingService;

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

    public function test_upsert_writes_local_embedding_when_generation_fails(): void
    {
        config([
            'ai.default_for_embeddings' => 'gemini',
            'ai.providers.gemini.embedding_deployment' => 'gemini-embedding-001',
        ]);

        $model = Work::factory()->create();

        $svc = new class extends EmbeddingService
        {
            public function generateEmbeddings(array $texts): ?array
            {
                return null; // Simulate provider failure
            }
        };

        $svc->upsertEmbeddingForModel($model, 'test content');

        $this->assertDatabaseHas('resume_embeddings', [
            'model_type' => Work::class,
            'model_id' => (string) $model->getKey(),
            'embedding_model' => 'database',
        ]);
    }

    public function test_upsert_stores_embedding_when_real_model_succeeds(): void
    {
        config([
            'ai.default_for_embeddings' => 'gemini',
            'ai.providers.gemini.embedding_deployment' => 'gemini-embedding-001',
        ]);

        $model = Work::factory()->create();

        $svc = new class extends EmbeddingService
        {
            public function generateEmbeddings(array $texts): ?array
            {
                return ['vectors' => [[0.1, 0.2, 0.3]], 'model' => 'gemini-embedding-001'];
            }
        };

        $svc->upsertEmbeddingForModel($model, 'Senior PHP Developer at Acme');

        $this->assertDatabaseHas('resume_embeddings', [
            'model_type' => Work::class,
            'model_id' => (string) $model->getKey(),
            'embedding_model' => 'gemini-embedding-001',
        ]);
    }
}
