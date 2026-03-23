<?php

namespace App\Observers;

use App\Models\ResumeKeyword;
use App\Services\Ai\EmbeddingService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ResumeModelObserver
{
    protected EmbeddingService $embeddings;

    public function __construct()
    {
        $this->embeddings = new EmbeddingService;
    }

    public function saved($model): void
    {
        // Create a short content summary to embed. Use model attributes commonly useful for CV.
        $summary = $this->buildSummary($model);

        if ($summary) {
            $this->embeddings->upsertEmbeddingForModel($model, $summary);
            $this->upsertKeywordsFromSummary($summary, $model);
        }
    }

    protected function upsertKeywordsFromSummary(string $summary, $model): void
    {
        $candidates = [];
        $verbs = [];

        // Try multiple sources for keyword extraction
        if (! $this->extractKeywordsFromModelMethods($model, $candidates)) {
            if (! $this->extractKeywordsFromLLMAgent($summary, $candidates, $verbs)) {
                $this->extractKeywordsFromNgrams($summary, $candidates);
            }
        }

        $this->persistKeywords($candidates, $verbs);
    }

    protected function extractKeywordsFromModelMethods($model, array &$candidates): bool
    {
        foreach (['suggestResumeKeywords', 'resumeKeywords', 'suggestKeywords'] as $method) {
            if (method_exists($model, $method)) {
                try {
                    $result = $model->{$method}();
                    if ($this->appendCandidates($result, $candidates)) {
                        return true;
                    }
                } catch (\Throwable $e) {
                    // ignore and continue to next method
                }
            }
        }

        return false;
    }

    protected function extractKeywordsFromLLMAgent(string $summary, array &$candidates, array &$verbs): bool
    {
        if (! function_exists('agent')) {
            return false;
        }

        try {
            $instr = "Given the following short resume summary, return a JSON object with two arrays: 'keywords' (short phrases or tokens useful to identify this CV) and 'verbs' (action/indicator verbs a user might use to ask about these skills). Example: {\"keywords\": [\"php 8.1\", \"machine learning\"], \"verbs\": [\"tiene\", \"ha trabajado\"] }. Only return valid JSON.";
            $response = agent(instructions: $instr)->prompt($summary);
            $text = is_object($response) && isset($response->text) ? (string) $response->text : (string) $response;

            return $this->parseAgentResponse($text, $candidates, $verbs);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function parseAgentResponse(string $text, array &$candidates, array &$verbs): bool
    {
        $json = json_decode($text, true);

        if (is_array($json)) {
            $this->appendArrayFromJson($json, 'keywords', $candidates);
            $this->appendArrayFromJson($json, 'verbs', $verbs);

            return ! empty($candidates);
        }

        // Fallback: try comma-separated format
        $parts = array_map('trim', preg_split('/[,;|\\n]+/', $text));
        foreach ($parts as $p) {
            if (mb_strlen($p) > 2) {
                $candidates[] = mb_strtolower($p);
            }
        }

        return ! empty($candidates);
    }

    protected function appendArrayFromJson(array $json, string $key, array &$target): void
    {
        if (! empty($json[$key]) && is_array($json[$key])) {
            foreach ($json[$key] as $item) {
                $item = trim(mb_strtolower((string) $item));
                if ($item !== '') {
                    $target[] = $item;
                }
            }
        }
    }

    protected function appendCandidates($result, array &$candidates): bool
    {
        if (is_iterable($result)) {
            foreach ($result as $r) {
                $r = trim(mb_strtolower((string) $r));
                if ($r !== '') {
                    $candidates[] = $r;
                }
            }

            return ! empty($candidates);
        }

        if (is_string($result) && $result !== '') {
            $candidates[] = trim(mb_strtolower($result));

            return true;
        }

        return false;
    }

    protected function extractKeywordsFromNgrams(string $summary, array &$candidates): void
    {
        $text = mb_strtolower($summary);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_values(array_filter($words, fn ($w) => mb_strlen($w) > 2));

        $this->extractNgramsFromWords($words, $candidates);
        $this->extractVersionTokens($text, $candidates);
    }

    protected function extractNgramsFromWords(array $words, array &$candidates): void
    {
        $max = min(100, count($words));
        for ($i = 0; $i < $max; $i++) {
            $candidates[] = $words[$i];
            if (isset($words[$i + 1])) {
                $candidates[] = $words[$i].' '.$words[$i + 1];
            }
            if (isset($words[$i + 2])) {
                $candidates[] = $words[$i].' '.$words[$i + 1].' '.$words[$i + 2];
            }
        }
    }

    protected function extractVersionTokens(string $text, array &$candidates): void
    {
        if (preg_match_all('/php\s?\d(?:\.\d)?/i', $text, $m)) {
            foreach (Arr::flatten($m) as $token) {
                $candidates[] = trim(mb_strtolower($token));
            }
        }
    }

    protected function persistKeywords(array $candidates, array $verbs): void
    {
        $candidates = $this->normalizeAndDeduplicate($candidates);
        foreach ($candidates as $kw) {
            ResumeKeyword::firstOrCreate(['keyword' => $kw], ['category' => 'resume']);
        }

        $verbs = $this->normalizeAndDeduplicate($verbs);
        foreach ($verbs as $verb) {
            ResumeKeyword::firstOrCreate(['keyword' => $verb], ['category' => 'verb']);
        }
    }

    protected function normalizeAndDeduplicate(array $items): array
    {
        return array_values(array_unique(array_filter(
            array_map(fn ($s) => trim((string) $s), $items)
        )));
    }

    protected function buildSummary($model): ?string
    {
        $attrs = $this->buildAttributesArray($model);

        if (empty($attrs)) {
            $attrs[] = $this->buildFallbackAttributes($model);
        }

        return $this->formatAttributes($attrs);
    }

    protected function buildAttributesArray($model): array
    {
        $attrs = [];

        $this->addPositionAndName($model, $attrs);
        $this->addSummary($model, $attrs);
        $this->addArrayAttributes($model, $attrs);
        $this->addStringAttributes($model, $attrs);

        return $attrs;
    }

    protected function addPositionAndName($model, array &$attrs): void
    {
        if (isset($model->position)) {
            $attrs[] = $model->position;
        }
        if (isset($model->name)) {
            $attrs[] = $model->name;
        }
    }

    protected function addSummary($model, array &$attrs): void
    {
        if (! empty($model->summary)) {
            $attrs[] = $model->summary;
        }
    }

    protected function addArrayAttributes($model, array &$attrs): void
    {
        foreach (['highlights', 'keywords', 'courses'] as $attribute) {
            $value = $model->{$attribute} ?? null;

            if (is_array($value) && $value !== []) {
                $attrs[] = implode('; ', array_slice($value, 0, 8));
            }
        }
    }

    protected function addStringAttributes($model, array &$attrs): void
    {
        foreach (['level', 'fluency', 'studyType', 'area', 'institution', 'organization', 'language'] as $attribute) {
            $value = $model->{$attribute} ?? null;

            if (is_string($value) && trim($value) !== '') {
                $attrs[] = $value;
            }
        }
    }

    protected function buildFallbackAttributes($model): string
    {
        $data = $model->toArray();
        $flat = [];

        foreach ($data as $k => $v) {
            if (is_scalar($v)) {
                $flat[] = $k.': '.$v;
            }
        }

        return implode(' | ', array_slice($flat, 0, 6));
    }

    protected function formatAttributes(array $attrs): ?string
    {
        $text = trim(implode('. ', array_values(array_unique(array_filter(
            array_map(static fn ($value) => is_string($value) ? trim($value) : '', $attrs)
        )))));

        return $text !== '' ? Str::limit($text, 1000) : null;
    }
}
