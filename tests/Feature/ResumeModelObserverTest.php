<?php

namespace Tests\Feature;

use App\Models\Basic;
use App\Models\ResumeEmbedding;
use App\Models\ResumeKeyword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

class ResumeModelObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_basic_creates_embedding_and_keywords(): void
    {
        config([
            'ai.default_for_embeddings' => 'gemini',
            'ai.providers.gemini.embedding_deployment' => 'gemini-embedding-001',
        ]);

        Embeddings::fake([
            [[0.1, 0.2, 0.3]],
        ]);

        $basic = Basic::factory()->create([
            'summary' => 'Laravel API platform builder with product leadership experience',
        ]);

        $embedding = ResumeEmbedding::query()
            ->where('model_type', Basic::class)
            ->where('model_id', (string) $basic->getKey())
            ->first();

        $this->assertNotNull($embedding);
        $this->assertSame('gemini-embedding-001', $embedding->embedding_model);
        $this->assertSame(3, $embedding->vector_length);
        $this->assertCount(3, $embedding->vector);
        $this->assertEqualsWithDelta(0.2672612419, $embedding->vector[0], 1e-9);
        $this->assertEqualsWithDelta(0.5345224838, $embedding->vector[1], 1e-9);
        $this->assertEqualsWithDelta(0.8017837257, $embedding->vector[2], 1e-9);
        $this->assertStringContainsString('Laravel API platform builder', $embedding->content ?? '');

        $this->assertGreaterThan(0, ResumeKeyword::query()->count());

        Embeddings::assertGenerated(function ($prompt) use ($basic): bool {
            return $prompt->provider->name() === 'gemini'
                && $prompt->model === 'gemini-embedding-001'
                && $prompt->count() === 1
                && str_contains($prompt->inputs[0], $basic->summary);
        });
    }
}
