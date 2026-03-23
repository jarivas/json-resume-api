<?php

namespace Tests\Unit;

use App\Services\Ai\EmbeddingService;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

class EmbeddingServiceTest extends TestCase
{
    /** @var class-string<EmbeddingService> */
    protected string $svcClass = EmbeddingService::class;

    public function test_normalize_vector_and_dot_product_and_equality(): void
    {
        $svc = new class extends EmbeddingService
        {
            public function exposeNormalize(array $v)
            {
                return $this->normalizeVector($v);
            }

            public function exposeDot(array $a, array $b)
            {
                return $this->dotProduct($a, $b);
            }

            public function exposeEqual(array $a, array $b, float $eps = 1e-6)
            {
                return $this->vectorsEqual($a, $b, $eps);
            }
        };

        $v = [3, 4];
        $norm = $svc->exposeNormalize($v);

        $this->assertEqualsWithDelta(3 / 5, $norm[0], 1e-9);
        $this->assertEqualsWithDelta(4 / 5, $norm[1], 1e-9);

        // dot product of normalized vector with itself == 1
        $dot = $svc->exposeDot($norm, $norm);
        $this->assertEqualsWithDelta(1.0, $dot, 1e-9);

        // equality with small perturbation
        $perturbed = [$norm[0] + 1e-7, $norm[1] - 1e-7];
        $this->assertTrue($svc->exposeEqual($norm, $perturbed));

        // big difference -> not equal
        $this->assertFalse($svc->exposeEqual($norm, [0.1, 0.2]));
    }

    public function test_resolve_embeddings_model_prefers_embedding_deployment_config(): void
    {
        config([
            'ai.default_for_embeddings' => 'openai',
            'ai.providers.openai.embedding_deployment' => 'embedding-from-config',
            'ai.providers.openai.models.embeddings.default' => 'embedding-from-models',
        ]);

        $svc = new class extends EmbeddingService
        {
            public function exposeResolveModel(string $provider): string
            {
                return $this->resolveEmbeddingsModel($provider);
            }
        };

        $this->assertSame('embedding-from-config', $svc->exposeResolveModel('openai'));
    }

    public function test_generate_embeddings_uses_sdk_api_with_configured_provider_and_model(): void
    {
        config([
            'ai.default' => 'openai',
            'ai.default_for_embeddings' => 'gemini',
            'ai.providers.openai.embedding_deployment' => 'openai-embedding-3-small',
            'ai.providers.gemini.embedding_deployment' => 'gemini-embedding-001',
        ]);

        Embeddings::fake([
            [[0.1, 0.2, 0.3]],
        ]);

        $result = (new EmbeddingService)->generateEmbeddings(['Laravel resume']);

        $this->assertSame([[0.1, 0.2, 0.3]], $result['vectors']);
        $this->assertSame('gemini-embedding-001', $result['model']);

        Embeddings::assertGenerated(function ($prompt): bool {
            return $prompt->provider->name() === 'gemini'
                && $prompt->model === 'gemini-embedding-001'
                && $prompt->inputs === ['Laravel resume'];
        });
    }

    public function test_generate_embeddings_prefers_default_embeddings_provider_over_default_text_provider(): void
    {
        config([
            'ai.default' => 'openai',
            'ai.default_for_embeddings' => 'azure',
            'ai.providers.openai.embedding_deployment' => 'openai-embedding-3-small',
            'ai.providers.azure.embedding_deployment' => 'azure-embedding-deployment',
        ]);

        Embeddings::fake([
            [[0.4, 0.5, 0.6]],
        ]);

        $result = (new EmbeddingService)->generateEmbeddings(['Vector check']);

        $this->assertSame([[0.4, 0.5, 0.6]], $result['vectors']);
        $this->assertSame('azure-embedding-deployment', $result['model']);

        Embeddings::assertGenerated(function ($prompt): bool {
            return $prompt->provider->name() === 'azure'
                && $prompt->model === 'azure-embedding-deployment'
                && $prompt->inputs === ['Vector check'];
        });
    }

    public function test_generate_embeddings_uses_local_database_mode_when_embedding_model_is_database(): void
    {
        config([
            'ai.default_for_embeddings' => 'gemini',
            'ai.providers.gemini.embedding_deployment' => 'database',
        ]);

        $result = (new EmbeddingService)->generateEmbeddings(['Local embedding mode']);

        $this->assertIsArray($result);
        $this->assertSame('database', $result['model']);
        $this->assertCount(1, $result['vectors']);
        $this->assertCount(64, $result['vectors'][0]);
        $this->assertGreaterThan(0.0, array_sum(array_map('abs', $result['vectors'][0])));
    }
}
