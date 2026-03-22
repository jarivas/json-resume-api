<?php

namespace Tests\Unit;

use App\Services\Ai\EmbeddingService;
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
            public function exposeResolveModel(object $provider): string
            {
                return $this->resolveEmbeddingsModel($provider);
            }
        };

        $provider = new class
        {
            public function name(): string
            {
                return 'openai';
            }

            public function defaultEmbeddingsModel(): string
            {
                return 'provider-default';
            }
        };

        $this->assertSame('embedding-from-config', $svc->exposeResolveModel($provider));
    }
}
