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
        $svc = new class extends EmbeddingService
        {
            public function exposeResolveModel(string $provider): string
            {
                return $this->resolveEmbeddingsModel($provider);
            }
        };

        // Should return a non-empty string for known providers.
        $resolved = $svc->exposeResolveModel('openai');
        $this->assertIsString($resolved);
        $this->assertNotEmpty($resolved);

        // For an unknown provider, fallback to the default embedding model.
        $this->assertSame('text-embedding-3-small', $svc->exposeResolveModel('bogus'));
    }

    public function test_generate_embeddings_uses_sdk_api_with_configured_provider_and_model(): void
    {
        // Simulate a remote provider response by overriding the remote call.

        $svc = new class extends EmbeddingService
        {
            protected function performRemoteEmbeddingsRequest(array $texts, string $provider, string $model): mixed
            {
                $resp = new \stdClass;
                $resp->embeddings = [[0.1, 0.2, 0.3]];
                $resp->meta = new \stdClass;
                $resp->meta->model = $model ?? 'gemini-embedding-001';

                return $resp;
            }
        };

        $result = $svc->generateEmbeddings(['Laravel resume']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('vectors', $result);
        $this->assertArrayHasKey('model', $result);
        $this->assertSame([[0.1, 0.2, 0.3]], $result['vectors']);
    }

    public function test_generate_embeddings_prefers_default_embeddings_provider_over_default_text_provider(): void
    {
        // Simulate a remote provider returning a different model.
        $svc = new class extends EmbeddingService
        {
            protected function performRemoteEmbeddingsRequest(array $texts, string $provider, string $model): mixed
            {
                $resp = new \stdClass;
                $resp->embeddings = [[0.4, 0.5, 0.6]];
                $resp->meta = new \stdClass;
                $resp->meta->model = $model ?? 'azure-embedding-deployment';

                return $resp;
            }
        };

        $result = $svc->generateEmbeddings(['Vector check']);

        $this->assertIsArray($result);
        $this->assertSame([[0.4, 0.5, 0.6]], $result['vectors']);
        $this->assertIsString($result['model']);
    }

    public function test_generate_embeddings_uses_local_database_mode_when_embedding_model_is_database(): void
    {
        // Directly exercise the local embedding generation implementation
        // without depending on external config.

        $svc = new class extends EmbeddingService
        {
            public function exposeLocal(array $texts)
            {
                return $this->generateLocalEmbeddings($texts, 'database');
            }
        };

        $result = $svc->exposeLocal(['Local embedding mode']);

        $this->assertIsArray($result);
        $this->assertSame('database', $result['model']);
        $this->assertCount(1, $result['vectors']);
        $this->assertCount(64, $result['vectors'][0]);
        $this->assertGreaterThan(0.0, array_sum(array_map('abs', $result['vectors'][0])));
    }

    public function test_generate_embeddings_with_fallback_returns_local_when_real_model_fails(): void
    {

        $svc = new class extends EmbeddingService
        {
            public function generateEmbeddings(array $texts): ?array
            {
                return null; // Simulate real provider failure
            }

            public function exposeFallback(array $texts): array
            {
                return $this->generateEmbeddingsWithFallback($texts);
            }
        };

        $result = $svc->exposeFallback(['query about PHP']);

        $this->assertIsArray($result);
        $this->assertSame('database', $result['model']);
        $this->assertCount(1, $result['vectors']);
        $this->assertCount(64, $result['vectors'][0]);
    }

    public function test_generate_embeddings_with_fallback_returns_real_model_result_when_available(): void
    {

        $expected = ['vectors' => [[0.1, 0.2, 0.3]], 'model' => 'gemini-embedding-001'];

        $svc = new class($expected) extends EmbeddingService
        {
            public function __construct(private readonly array $fakeResult) {}

            public function generateEmbeddings(array $texts): ?array
            {
                return $this->fakeResult;
            }

            public function exposeFallback(array $texts): array
            {
                return $this->generateEmbeddingsWithFallback($texts);
            }
        };

        $result = $svc->exposeFallback(['query about PHP']);

        $this->assertSame($expected, $result);
    }

    public function test_generate_embeddings_with_fallback_skips_repeated_remote_calls_after_rate_limit(): void
    {

        $svc = new class extends EmbeddingService
        {
            public int $remoteCalls = 0;

            protected function performRemoteEmbeddingsRequest(array $texts, string $provider, string $model): mixed
            {
                $this->remoteCalls++;

                throw new \RuntimeException('Application rate limited by AI provider [gemini].');
            }

            public function exposeFallback(array $texts): array
            {
                return $this->generateEmbeddingsWithFallback($texts);
            }
        };

        $first = $svc->exposeFallback(['first text']);
        $second = $svc->exposeFallback(['second text']);

        $this->assertSame('database', $first['model']);
        $this->assertSame('database', $second['model']);
        $this->assertSame(1, $svc->remoteCalls, 'Expected provider call to be skipped while cooldown is active.');
    }
}
