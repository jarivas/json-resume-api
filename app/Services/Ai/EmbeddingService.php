<?php

namespace App\Services\Ai;

use App\Models\ResumeEmbedding;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

class EmbeddingService
{
    /**
     * Generate embeddings for text using the configured AI provider.
     * Returns the vector (array) or null on failure.
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            $batch = $this->generateEmbeddings([$text]);

            if (is_array($batch) && ! empty($batch['vectors'][0])) {
                return $batch['vectors'][0];
            }
        } catch (\Throwable $e) {
            Log::error('Embedding generation failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Generate embeddings for multiple texts in a single call when supported.
     * Returns ['vectors' => array, 'model' => string] or null on failure.
     */
    public function generateEmbeddings(array $texts): ?array
    {
        try {
            $provider = $this->resolveEmbeddingsProvider();
            $model = $this->resolveEmbeddingsModel($provider);
            $response = Embeddings::for($texts)->generate($provider, $model);

            $normalized = [];

            foreach ($response->embeddings as $vector) {
                $normalized[] = array_map(fn ($value) => (float) $value, $vector);
            }

            return [
                'vectors' => $normalized,
                'model' => $response->meta->model,
            ];
        } catch (\Throwable $e) {
            Log::error('Embedding batch generation failed: '.$e->getMessage());
        }

        return null;
    }

    protected function resolveEmbeddingsProvider(): string
    {
        $provider = config('ai.default_for_embeddings', config('ai.default', 'openai'));

        return is_string($provider) && trim($provider) !== ''
            ? trim($provider)
            : 'openai';
    }

    protected function resolveEmbeddingsModel(string $providerName): string
    {
        $configuredModel = config("ai.providers.{$providerName}.embedding_deployment")
            ?? config("ai.providers.{$providerName}.models.embeddings.default");

        if (is_string($configuredModel) && trim($configuredModel) !== '') {
            return $configuredModel;
        }

        return 'text-embedding-3-small';
    }

    /**
     * Persist or update an embedding record for a model instance.
     */
    public function upsertEmbeddingForModel(object $model, string $content): void
    {
        $batch = $this->generateEmbeddings([$content]);

        $vector = null;
        $embeddingModel = null;

        if (is_array($batch)) {
            $embeddingModel = $batch['model'] ?? null;
            $vector = $batch['vectors'][0] ?? null;
            if (is_array($vector) && count($vector) > 0) {
                $vector = $this->normalizeVector($vector);
            }
        }

        $where = [
            'model_type' => get_class($model),
            'model_id' => (string) $model->getKey(),
        ];

        $existing = ResumeEmbedding::where($where)->first();

        if ($existing && is_array($existing->vector) && is_array($vector)) {
            if ($this->vectorsEqual($existing->vector, $vector)) {
                // Vector unchanged -> avoid unnecessary DB write
                return;
            }
        }

        ResumeEmbedding::updateOrCreate($where, [
            'content' => $content,
            'vector' => $vector,
            'vector_length' => is_array($vector) ? count($vector) : null,
            'embedding_model' => $embeddingModel,
        ]);
    }

    /**
     * Given a query string, compute its embedding and return the top-N most similar
     * resume fragments from `resume_embeddings` with their similarity scores.
     * Returns array of ['record' => ResumeEmbedding, 'score' => float].
     */
    public function findMostSimilar(string $query, int $limit = 5): array
    {
        $batch = $this->generateEmbeddings([$query]);
        $qVec = $batch['vectors'][0] ?? null;

        if (is_array($qVec)) {
            $qVec = $this->normalizeVector($qVec);
        }

        $rows = ResumeEmbedding::query()->get();
        $scores = [];

        foreach ($rows as $row) {
            $score = 0.0;
            $lexicalScore = $this->textSimilarity($query, $row->content ?? '');
            $vec = $row->vector;

            if (is_array($qVec) && is_array($vec) && count($vec) === count($qVec) && count($vec) > 0) {
                $semanticScore = max(0.0, $this->dotProduct($qVec, $vec));

                // Keep semantic ranking, but require some lexical overlap to avoid high false positives.
                $score = $lexicalScore > 0.0
                    ? ($semanticScore + $lexicalScore) / 2
                    : 0.0;
            } else {
                $score = $lexicalScore;
            }

            $scores[] = ['record' => $row, 'score' => $score];
        }

        usort($scores, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scores, 0, $limit);
    }

    /**
     * Compute cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        // Keep for compatibility — compute cosine directly
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;

        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $ai = (float) $a[$i];
            $bi = (float) $b[$i];
            $dot += $ai * $bi;
            $na += $ai * $ai;
            $nb += $bi * $bi;
        }

        if ($na <= 0 || $nb <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($na) * sqrt($nb));
    }

    protected function dotProduct(array $a, array $b): float
    {
        $dot = 0.0;
        for ($i = 0, $len = count($a); $i < $len; $i++) {
            $dot += ((float) $a[$i]) * ((float) $b[$i]);
        }

        return $dot;
    }

    protected function normalizeVector(array $v): array
    {
        $sum = 0.0;
        foreach ($v as $x) {
            $f = (float) $x;
            $sum += $f * $f;
        }

        if ($sum <= 0.0) {
            return array_map(fn ($x) => (float) $x, $v);
        }

        $norm = sqrt($sum);

        return array_map(fn ($x) => ((float) $x) / $norm, $v);
    }

    protected function vectorsEqual(array $a, array $b, float $eps = 1e-6): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        for ($i = 0, $len = count($a); $i < $len; $i++) {
            if (abs(((float) $a[$i]) - ((float) $b[$i])) > $eps) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lightweight textual similarity using token Jaccard index.
     */
    protected function textSimilarity(string $a, string $b): float
    {
        $tokensA = $this->tokenize($a);
        $tokensB = $this->tokenize($b);

        if (empty($tokensA) || empty($tokensB)) {
            return 0.0;
        }

        $intersect = array_intersect($tokensA, $tokensB);
        $union = array_unique(array_merge($tokensA, $tokensB));

        return count($intersect) / max(1, count($union));
    }

    protected function tokenize(string $text): array
    {
        $stopWords = [
            'a', 'al', 'and', 'con', 'de', 'del', 'el', 'en', 'for', 'ha', 'he', 'la', 'las',
            'los', 'para', 'por', 'que', 'qué', 'the', 'with', 'una', 'uno', 'unos', 'unas', 'y',
        ];

        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
        $parts = array_filter(array_map('trim', explode(' ', $text)));

        return array_values(array_filter($parts, function ($token) use ($stopWords) {
            return mb_strlen($token) > 2 && ! in_array($token, $stopWords, true);
        }));
    }
}
